<?php
declare(strict_types = 1);

namespace Nelliel\Content;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\ArchiveAndPrune;
use Nelliel\Cites;
use Nelliel\Moar;
use Nelliel\SQLCompatibility;
use Nelliel\SQLHelpers;
use Nelliel\API\JSON\PostJSON;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPost;
use Nelliel\Setup\TablePosts;
use PDO;

class Post
{
    protected $content_id;
    protected $database;
    protected $domain;
    protected $content_data = array();
    protected $content_moar;
    protected $authorization;
    protected $main_table;
    protected $archive_prune;
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
        $this->main_table = new TablePosts($this->database, new SQLCompatibility($this->database));
        $this->main_table->tableName($domain->reference('posts_table'));
        $this->parent = $parent;
        $this->json = new PostJSON($this, nel_utilities()->fileHandler());
        $this->sql_helpers = new SQLHelpers($this->database);

        if ($load)
        {
            $this->loadFromDatabase();
        }

        $this->archive_prune = new ArchiveAndPrune($this->domain, nel_utilities()->fileHandler());
    }

    public function loadFromDatabase()
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $this->domain->reference('posts_table') . '" WHERE "post_number" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()], PDO::FETCH_ASSOC);

        if (empty($result))
        {
            return false;
        }

        $result['ip_address'] = nel_convert_ip_from_storage($result['ip_address']);
        $column_types = $this->main_table->columnTypes();

        foreach ($result as $name => $value)
        {
            $this->content_data[$name] = nel_cast_to_datatype($value, $column_types[$name]['php_type'] ?? '');
        }

        $moar = $result['moar'] ?? '';
        $this->getMoar()->storeFromJSON($moar);
        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (!$this->isLoaded() || empty($this->content_id->postID()))
        {
            return false;
        }

        $filtered_data = $this->main_table->filterColumns($this->content_data);
        $filtered_data['ip_address'] = nel_prepare_ip_for_storage($this->data('ip_address'));
        $column_list = array_keys($filtered_data);
        $values = array_values($filtered_data);

        if ($this->main_table->rowExists($filtered_data))
        {
            $where_columns = ['post_number'];
            $where_keys = ['where_post_number'];
            $where_values = [$this->content_id->postID()];
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
                nel_derp(62, _gettext('Cannot remove post. Board is locked.'));
            }

            $session = new \Nelliel\Account\Session();
            $user = $session->user();
            $bypass = false;

            if ($user && $session->user()->checkPermission($this->domain, 'perm_bypass_renzoku'))
            {
                $bypass = true;
            }

            $delete_post_renzoku = $this->domain->setting('delete_post_renzoku');

            if (!$bypass && $delete_post_renzoku > 0 && time() - $this->content_data['post_time'] < $delete_post_renzoku)
            {
                nel_derp(64,
                        sprintf(_gettext('You must wait %d seconds after making a post before it can be deleted.'),
                                $delete_post_renzoku));
            }
        }

        // It's possible to have a thread with no posts but for now we don't use that capability
        if ($this->data('op'))
        {
            $this->getParent()->remove(true);
        }
        else
        {
            $this->removeFromDatabase();
            $this->removeFromDisk();
        }

        $this->archive_prune->updateThreads();
        return true;
    }

    protected function removeFromDatabase()
    {
        if (empty($this->content_id->postID()))
        {
            return false;
        }

        $prepared = $this->database->prepare(
                'DELETE FROM "' . $this->domain->reference('posts_table') . '" WHERE "post_number" = ?');
        $this->database->executePrepared($prepared, [$this->content_id->postID()]);
        $cites = new Cites($this->database);
        $cites->updateForPost($this);
        $cites->removeForPost($this);
        return true;
    }

    protected function removeFromDisk()
    {
        $file_handler = nel_utilities()->fileHandler();
        $file_handler->eraserGun($this->srcPath());
        $file_handler->eraserGun($this->previewPath());
    }

    public function verifyModifyPerms()
    {
        $session = new \Nelliel\Account\Session();
        $user = $session->user();

        if (empty($this->content_data))
        {
            $this->loadFromDatabase();
        }

        $flag = false;

        if ($session->isActive())
        {
            if ($user->checkPermission($this->domain, 'perm_delete_posts'))
            {
                if (!$user->isSiteOwner() && !empty($this->content_data['account_id']) &&
                        $this->authorization->userExists($this->content_data['account_id']))
                {
                    $mod_post_user = $this->authorization->getUser($this->content_data['account_id']);
                    $flag = $this->authorization->roleLevelCheck($user->getDomainRole($this->domain)->id(),
                            $mod_post_user->getDomainRole($this->domain)->id());
                }
                else
                {
                    $flag = true;
                }
            }
        }

        $update_sekrit = $_POST['update_sekrit'] ?? '';

        if (!$flag)
        {
            if (!isset($this->content_data['post_password']) ||
                    !hash_equals($this->content_data['post_password'], nel_post_password_hash($update_sekrit)) ||
                    !$this->domain->setting('user_delete_own'))
            {
                nel_derp(60, _gettext('Password is wrong or you are not allowed to delete that.'));
            }
        }

        return true;
    }

    public function getParent(): Thread
    {
        if (is_null($this->parent))
        {
            $content_id = new ContentID();
            $content_id->changeThreadID($this->content_id->threadID());
            $this->parent = new Thread($content_id, $this->domain);
        }

        return $this->parent;
    }

    public function reserveDatabaseRow($post_time, $post_time_milli, $hashed_ip_address, $temp_database = null)
    {
        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare(
                'INSERT INTO "' . $this->domain->reference('posts_table') .
                '" ("post_time", "post_time_milli", "hashed_ip_address") VALUES (?, ?, ?)');
        $database->executePrepared($prepared, [$post_time, $post_time_milli, $hashed_ip_address]);
        $prepared = $database->prepare(
                'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "post_time" = ? AND "post_time_milli" = ? AND "hashed_ip_address" = ?');
        $result = $database->executePreparedFetch($prepared, [$post_time, $post_time_milli, $hashed_ip_address],
                PDO::FETCH_COLUMN, true);
        $this->content_id->changeThreadID(
                ($this->content_id->threadID() === 0) ? $result : $this->content_id->threadID());
        $this->changeData('parent_thread', $this->content_id->threadID());
        $this->content_id->changePostID($result);
    }

    public function updateCounts()
    {
        $prepared = $this->database->prepare(
                'SELECT COUNT("entry") FROM "' . $this->domain->reference('upload_table') . '" WHERE "post_ref" = ?');
        $total_uploads = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()],
                PDO::FETCH_COLUMN, true);

        $prepared = $this->database->prepare(
                'SELECT COUNT("entry") FROM "' . $this->domain->reference('upload_table') .
                '" WHERE "post_ref" = ? AND "embed_url" IS NULL');
        $file_count = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()], PDO::FETCH_COLUMN,
                true);

        $prepared = $this->database->prepare(
                'SELECT COUNT("entry") FROM "' . $this->domain->reference('upload_table') .
                '" WHERE "post_ref" = ? AND "embed_url" IS NOT NULL');
        $embed_count = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()],
                PDO::FETCH_COLUMN, true);

        $prepared = $this->database->prepare(
                'UPDATE "' . $this->domain->reference('posts_table') .
                '" SET "total_uploads" = ?, "file_count" = ?, "embed_count" = ? WHERE "post_number" = ?');
        $this->database->executePrepared($prepared,
                [$total_uploads, $file_count, $embed_count, $this->content_id->postID()]);
    }

    public function convertToThread(): Thread
    {
        $time = nel_get_microtime();
        $new_content_id = new ContentID();
        $new_content_id->changeThreadID($this->content_id->postID());
        $new_content_id->changePostID($this->content_id->postID());
        $new_thread = new Thread($new_content_id, $this->domain);
        $new_thread->changeData('thread_id', $this->content_id->postID());
        $new_thread->changeData('last_bump_time', $time['time']);
        $new_thread->changeData('last_bump_time_milli', $time['milli']);
        $new_thread->changeData('last_update', $time['time']);
        $new_thread->changeData('last_update_milli', $time['milli']);
        $new_thread->writeToDatabase();
        $new_thread->loadFromDatabase();
        $new_thread->createDirectories();
        $uploads = $this->getUploads();

        foreach ($uploads as $upload)
        {
            $upload->changeData('parent_thread', $new_thread->content_id->threadID());
            $upload->writeToDatabase();
        }

        $this->loadFromDatabase();
        $this->content_id->changeThreadID($new_thread->content_id->threadID());
        $this->changeData('parent_thread', $this->content_id->threadID());
        $this->changeData('op', 1);
        $this->writeToDatabase();
        $new_thread->updateCounts();
        $this->archive_prune->updateThreads();
        return $new_thread;
    }

    public function createDirectories()
    {
        $file_handler = nel_utilities()->fileHandler();
        $file_handler->createDirectory($this->srcPath(), NEL_DIRECTORY_PERM, true);
        $file_handler->createDirectory($this->previewPath(), NEL_DIRECTORY_PERM, true);
    }

    public function getCache(): array
    {
        $prepared = $this->database->prepare(
                'SELECT "cache" FROM "' . $this->domain->reference('posts_table') . '" WHERE "post_number" = ?');
        $cache = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()], PDO::FETCH_COLUMN);

        if (is_string($cache))
        {
            return json_decode($cache, true);
        }

        return array();
    }

    public function storeCache(): void
    {
        $cache_array = array();
        $output_post = new OutputPost($this->domain, false);
        $cache_array['comment_data'] = $output_post->parseComment($this->data('comment'), $this->content_id);
        $cache_array['backlink_data'] = $output_post->generateBacklinks($this);
        $encoded_cache = json_encode($cache_array, JSON_UNESCAPED_UNICODE);
        $prepared = $this->database->prepare(
                'UPDATE "' . $this->domain->reference('posts_table') .
                '" SET "cache" = ?, "regen_cache" = 0 WHERE "post_number" = ?');
        $this->database->executePrepared($prepared, [$encoded_cache, $this->content_id->postID()]);
    }

    public function srcPath(): string
    {
        return $this->domain->reference('src_path') . $this->content_id->threadID() . '/' . $this->content_id->postID() .
                '/';
    }

    public function previewPath(): string
    {
        return $this->domain->reference('preview_path') . $this->content_id->threadID() . '/' .
                $this->content_id->postID() . '/';
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

    protected function isLoaded()
    {
        return !empty($this->content_data);
    }

    public function getUploads(): array
    {
        $uploads = array();
        $prepared = $this->database->prepare(
                'SELECT "upload_order" FROM "' . $this->domain->reference('upload_table') .
                '" WHERE "post_ref" = ? ORDER BY "upload_order" ASC');
        $upload_list = $this->database->executePreparedFetchAll($prepared, [$this->contentID()->postID()],
                PDO::FETCH_COLUMN);

        foreach ($upload_list as $id)
        {
            $content_id = new ContentID(
                    ContentID::createIDString($this->content_id->threadID(), $this->content_id->postID(), intval($id)));
            $uploads[] = $content_id->getInstanceFromID($this->domain);
        }

        return $uploads;
    }

    public function getJSON(): PostJSON
    {
        return $this->json;
    }
}