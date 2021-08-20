<?php
declare(strict_types = 1);

namespace Nelliel\Content;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\ArchiveAndPrune;
use Nelliel\Cites;
use Nelliel\Moar;
use Nelliel\Overboard;
use Nelliel\SQLCompatibility;
use Nelliel\SQLHelpers;
use Nelliel\API\JSON\ThreadJSON;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Tables\TableThreads;
use PDO;

class Thread
{
    protected $content_id;
    protected $database;
    protected $domain;
    protected $content_data = array();
    protected $content_moar;
    protected $authorization;
    protected $main_table;
    protected $archive_prune;
    protected $overboard;
    protected $parent;
    protected $json;
    protected $sql_helpers;

    function __construct(ContentID $content_id, Domain $domain, Thread $parent = null, bool $load = true)
    {
        $this->database = $domain->database();
        $this->content_id = $content_id;
        $this->domain = $domain;
        $this->authorization = new Authorization($this->database);
        $this->storeMoar(new Moar());
        $this->main_table = new TableThreads($this->database, new SQLCompatibility($this->database));
        $this->main_table->tableName($domain->reference('threads_table'));
        $this->parent = $parent;
        $this->json = new ThreadJSON($this, nel_utilities()->fileHandler());
        $this->sql_helpers = new SQLHelpers($this->database);

        if ($load)
        {
            $this->loadFromDatabase();
        }

        $this->archive_prune = new ArchiveAndPrune($this->domain, nel_utilities()->fileHandler());
        $this->overboard = new Overboard($this->database);
    }

    public function exists()
    {
        return !empty($this->content_data);
    }

    public function loadFromDatabase()
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $this->domain->reference('threads_table') . '" WHERE "thread_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()], PDO::FETCH_ASSOC);

        if (empty($result))
        {
            return false;
        }

        $column_types = $this->main_table->columnTypes();

        foreach ($result as $name => $value)
        {
            $this->content_data[$name] = nel_cast_to_datatype($value, $column_types[$name]['php_type'] ?? '');
        }

        $moar = $result['moar'] ?? '';
        $this->getMoar()->storeFromJSON($moar);
        return true;
    }

    public function writeToDatabase()
    {
        if (!$this->isLoaded() || empty($this->content_id->threadID()))
        {
            return false;
        }

        $filtered_data = $this->main_table->filterColumns($this->content_data);
        $column_list = array_keys($filtered_data);
        $values = array_values($filtered_data);

        if ($this->main_table->rowExists($filtered_data))
        {
            $where_columns = ['thread_id'];
            $where_keys = ['where_thread_id'];
            $where_values = [$this->content_id->threadID()];
            $prepared = $this->sql_helpers->buildPreparedUpdate($this->main_table->tableName(), $column_list,
                    $where_columns, $where_keys);
            $this->sql_helpers->bindToPrepared($prepared, $column_list, $values);
            $this->sql_helpers->bindToPrepared($prepared, $where_keys, $where_values);
            $this->database->executePrepared($prepared);
        }
        else
        {
            $prepared = $this->sql_helpers->buildPreparedInsert($this->main_table->tableName(), $column_list);
            $this->sql_helpers->bindToPrepared($prepared, $column_list, $values);
            $this->database->executePrepared($prepared);
        }

        return true;
    }

    public function remove(bool $perm_override = false)
    {
        if (!$perm_override)
        {
            if (!$this->verifyModifyPerms())
            {
                return false;
            }

            if ($this->domain->reference('locked'))
            {
                nel_derp(63, _gettext('Cannot remove thread. Board is locked.'));
            }
        }

        $this->removeFromDatabase();
        $this->removeFromDisk();
        $this->archive_prune->updateThreads();
        return true;
    }

    protected function removeFromDatabase()
    {
        if (empty($this->content_id->threadID()))
        {
            return false;
        }

        $prepared = $this->database->prepare(
                'DELETE FROM "' . $this->domain->reference('threads_table') . '" WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$this->content_id->threadID()]);
        $cites = new Cites($this->database);
        $cites->updateForThread($this);
        $cites->removeForThread($this);
        return true;
    }

    protected function removeFromDisk()
    {
        $file_handler = nel_utilities()->fileHandler();
        $file_handler->eraserGun($this->srcPath());
        $file_handler->eraserGun($this->previewPath());
        $file_handler->eraserGun($this->pagePath());
    }

    public function verifyModifyPerms()
    {
        $post = new Post($this->content_id, $this->domain);
        $post->content_id->changePostID($this->firstPostID());
        return $post->verifyModifyPerms();
    }

    public function getParent(): Thread
    {
        return $this;
    }

    public function updateCounts()
    {
        $prepared = $this->database->prepare(
                'SELECT COUNT("post_number") FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "parent_thread" = ?');
        $post_count = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);

        $prepared = $this->database->prepare(
                'UPDATE "' . $this->domain->reference('threads_table') . '" SET "post_count" = ? WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$post_count, $this->content_id->threadID()]);

        $prepared = $this->database->prepare(
                'SELECT COUNT("entry") FROM "' . $this->domain->reference('upload_table') . '" WHERE "parent_thread" = ?');
        $total_uploads = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);

        $prepared = $this->database->prepare(
                'SELECT COUNT("entry") FROM "' . $this->domain->reference('upload_table') .
                '" WHERE "parent_thread" = ? AND "embed_url" IS NOT NULL');
        $embed_count = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);

        $file_count = $total_uploads - $embed_count;
        $prepared = $this->database->prepare(
                'UPDATE "' . $this->domain->reference('threads_table') .
                '" SET "total_uploads" = ?, "file_count" = ?, "embed_count" = ? WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared,
                [$total_uploads, $file_count, $embed_count, $this->content_id->threadID()]);
    }

    public function createDirectories()
    {
        $file_handler = nel_utilities()->fileHandler();
        $file_handler->createDirectory($this->pagePath(), NEL_DIRECTORY_PERM, true);
    }

    public function toggleSticky(): bool
    {
        if (!$this->isLoaded())
        {
            return false;
        }

        $this->content_data['sticky'] = ($this->content_data['sticky'] == 0) ? 1 : 0;
        $success = $this->writeToDatabase();
        $this->archive_prune->updateThreads();
        return $success;
    }

    public function toggleLock(): bool
    {
        $this->changeData('locked', !$this->data('locked'));
        return $this->writeToDatabase();
    }

    public function toggleSage(): bool
    {
        $this->changeData('sage', !$this->data('sage'));
        return $this->writeToDatabase();
    }

    public function toggleCyclic(): bool
    {
        $this->changeData('cyclic', !$this->data('cyclic'));
        return $this->writeToDatabase();
    }

    public function cycle(): void
    {
        $prepared = $this->database->prepare(
                'SELECT "post_number", "op" FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "parent_thread" = ? ORDER BY "post_number" DESC');
        $descending_post_list = $this->database->executePreparedFetchAll($prepared, [$this->content_id->threadID()],
                PDO::FETCH_ASSOC);
        $post_count = count($descending_post_list);
        $bump_limit = $this->domain->setting('max_posts');

        if ($post_count > $bump_limit)
        {
            $old_post_list = array_slice($descending_post_list, $bump_limit - 1);

            foreach ($old_post_list as $old_post)
            {
                if ($old_post['op'] == 1)
                {
                    continue;
                }

                $post_content_id = new ContentID(
                        ContentID::createIDString($this->content_id->threadID(), $old_post['post_number'], 0));
                $post = $post_content_id->getInstanceFromID($this->domain);
                $post->remove(true);
            }
        }
    }

    public function firstPostID(): int
    {
        $prepared = $this->database->prepare(
                'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "parent_thread" = ? AND "op" = 1');
        $first_post = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);
        return intval($first_post);
    }

    public function lastPostID(): int
    {
        $prepared = $this->database->prepare(
                'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "parent_thread" = ? ORDER BY "post_number" DESC LIMIT 1');
        $last_post = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);
        return intval($last_post);
    }

    public function lastBumpPostID(): int
    {
        $prepared = $this->database->prepare(
                'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "parent_thread" = ? AND "sage" = 0 ORDER BY "post_number" DESC LIMIT 1');
        $last_bump_post = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);
        return intval($last_bump_post);
    }

    public function getNthPostID(int $nth_post): int
    {
        $prepared = $this->database->prepare(
                'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
        $post_list = $this->database->executePreparedFetchAll($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);
        $nth_post_index = $nth_post - 1;

        if ($post_list === false || !isset($post_list[$nth_post_index]))
        {
            return 0;
        }

        return intval($post_list[$nth_post_index]);
    }

    // Most of this is based on vichan's slugify
    public function generateSlug(Post $post): string
    {
        $slug = '';
        $max_length = $this->domain->setting('max_slug_length');

        if (!nel_true_empty($post->data('subject')))
        {
            $base_text = $post->data('subject');
        }
        else if (!nel_true_empty($post->data('comment')))
        {
            $base_text = $post->data('comment');
        }
        else
        {
            $base_text = '';
        }

        // Convert non-ASCII to ASCII equivalents if possible
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base_text);

        // Only keep alphanumeric characters
        $slug = preg_replace('/[^a-zA-Z0-9]/', '-', $slug);

        // Replace multiple dashes with single ones
        $slug = preg_replace('/-+/', '-', $slug);

        // Strip dashes at the beginning and at the end
        $slug = preg_replace('/^-|-$/', '', $slug);

        // Limit slug length, attempting to not break words
        $matches = array();
        preg_match('/^(.{0,' . $max_length . '})\b(?=\W|$)/', $slug, $matches);

        // If the base text is actually one really long word or something, just truncate it
        if (empty($matches))
        {
            $slug = substr($slug, 0, $max_length);
        }
        else
        {
            $slug = $matches[1];
        }

        // Make lowercase
        $slug = strtolower($slug);

        return $slug;
    }

    public function pageBasename(): string
    {
        $page_filename = '';
        $slug = $this->content_data['slug'] ?? '';

        if ($this->domain->setting('slugify_thread_url') && !nel_true_empty($slug))
        {
            $page_filename = $this->content_data['slug'];
        }
        else
        {
            $page_filename = sprintf(nel_site_domain()->setting('thread_filename_format'), $this->content_id->threadID());
        }

        return $page_filename;
    }

    public function getURL(): string
    {
        $base_path = $this->domain->reference('page_web_path') . $this->content_id->threadID() . '/';
        return $base_path . $this->pageBasename() . NEL_PAGE_EXT;
    }

    public function pagePath()
    {
        return $this->domain->reference('page_path') . $this->content_id->threadID() . '/';
    }

    public function srcPath()
    {
        return $this->domain->reference('src_path') . $this->content_id->threadID() . '/';
    }

    public function previewPath()
    {
        return $this->domain->reference('preview_path') . $this->content_id->threadID() . '/';
    }

    public function archive()
    {
    }

    public function storeMoar(Moar $moar)
    {
        $this->content_moar = $moar;
    }

    public function getMoar()
    {
        return $this->content_moar;
    }

    protected function contentDataOrDefault(string $data_name, $default)
    {
        if (isset($this->content_data[$data_name]))
        {
            return $this->content_data[$data_name];
        }

        return $default;
    }

    public function data(string $key)
    {
        return $this->content_data[$key] ?? null;
    }

    public function changeData(string $key, $new_data)
    {
        $column_types = $this->main_table->columnTypes();
        $type = $column_types[$key]['php_type'] ?? '';
        $new_data = nel_cast_to_datatype($new_data, $type);
        $old_data = $this->data($key);
        $this->content_data[$key] = $new_data;
        return $old_data;
    }

    public function contentID()
    {
        return $this->content_id;
    }

    public function domain()
    {
        return $this->domain;
    }

    public function isLoaded()
    {
        return !empty($this->content_data);
    }

    public function getPosts(): array
    {
        $posts = array();
        $prepared = $this->database->prepare(
                'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
        $post_list = $this->database->executePreparedFetchAll($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);

        foreach ($post_list as $id)
        {
            $content_id = new ContentID(ContentID::createIDString($this->content_id->threadID(), intval($id)));
            $posts[] = $content_id->getInstanceFromID($this->domain);
        }

        return $posts;
    }

    public function getJSON(): ThreadJSON
    {
        return $this->json;
    }
}