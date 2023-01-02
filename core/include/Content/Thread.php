<?php
declare(strict_types = 1);

namespace Nelliel\Content;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\ArchiveAndPrune;
use Nelliel\FGSFDS;
use Nelliel\Moar;
use Nelliel\Overboard;
use Nelliel\API\JSON\ThreadJSON;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Tables\TableThreads;
use PDO;
use Nelliel\Regen;
use Nelliel\Statistics;
use Nelliel\Cites;

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
    protected $parent = null;
    protected $json;
    protected $sql_helpers;

    function __construct(ContentID $content_id, Domain $domain, bool $load = true)
    {
        $this->database = $domain->database();
        $this->content_id = $content_id;
        $this->domain = $domain;
        $this->authorization = new Authorization($this->database);
        $this->storeMoar(new Moar());
        $this->main_table = new TableThreads($this->database, nel_utilities()->sqlCompatibility());
        $this->main_table->tableName($domain->reference('threads_table'));
        $this->json = new ThreadJSON($this);
        $this->sql_helpers = nel_utilities()->sqlHelpers();

        if ($load) {
            $this->loadFromDatabase(true);
        }

        $this->archive_prune = new ArchiveAndPrune($this->domain, nel_utilities()->fileHandler());
        $this->overboard = new Overboard($this->database);
    }

    public function exists(): bool
    {
        return $this->loadFromDatabase(false);
    }

    public function loadFromDatabase(bool $populate = true): bool
    {
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . $this->domain->reference('threads_table') . '" WHERE "thread_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()], PDO::FETCH_ASSOC);

        if (empty($result)) {
            return false;
        }

        if (!$populate) {
            return true;
        }

        $column_types = $this->main_table->columnTypes();

        foreach ($result as $name => $value) {
            $this->content_data[$name] = nel_typecast($value, $column_types[$name]['php_type'] ?? '');
        }

        $moar = $result['moar'] ?? '';
        $this->getMoar()->storeFromJSON($moar);
        return true;
    }

    public function writeToDatabase(): bool
    {
        if (!$this->isLoaded() || empty($this->content_id->threadID())) {
            return false;
        }

        $filtered_data = $this->main_table->filterColumns($this->content_data);
        $filtered_data['moar'] = $this->getMoar()->getJSON();
        $pdo_types = $this->main_table->getPDOTypes($filtered_data);
        $column_list = array_keys($filtered_data);
        $values = array_values($filtered_data);

        if ($this->main_table->rowExists($filtered_data)) {
            $where_columns = ['thread_id'];
            $where_keys = ['where_thread_id'];
            $where_values = [$this->content_id->threadID()];
            $prepared = $this->sql_helpers->buildPreparedUpdate($this->main_table->tableName(), $column_list,
                $where_columns, $where_keys);
            $this->sql_helpers->bindToPrepared($prepared, $column_list, $values, $pdo_types);
            $this->sql_helpers->bindToPrepared($prepared, $where_keys, $where_values);
            $this->database->executePrepared($prepared);
        } else {
            $prepared = $this->sql_helpers->buildPreparedInsert($this->main_table->tableName(), $column_list);
            $this->sql_helpers->bindToPrepared($prepared, $column_list, $values, $pdo_types);
            $this->database->executePrepared($prepared);
        }

        return true;
    }

    public function delete(bool $perm_override = false, bool $parent_delete = false): bool
    {
        if (!$perm_override) {
            if (!$this->verifyModifyPerms()) {
                return false;
            }

            if ($this->domain->reference('locked')) {
                nel_derp(63, _gettext('Cannot delete thread. Board is locked.'));
            }

            if ($this->domain->setting('thread_no_delete_replies') > 0 &&
                $this->data('post_count') - 1 >= $this->domain->setting('thread_no_delete_replies')) {
                nel_derp(65, _gettext('Thread has reached reply threshold and cannot be deleted.'));
            }

            $time_since_op = time() - $this->firstPost()->data('post_time');

            if ($this->domain->setting('thread_no_delete_time') > 0 &&
                $time_since_op > $this->domain->setting('thread_no_delete_time')) {
                nel_derp(66, _gettext('Thread has reached age threshold and cannot be deleted.'));
            }
        }

        $posts = $this->getPosts();

        foreach ($posts as $post) {
            $post->delete(true, true);
        }

        $this->deleteFromDatabase($parent_delete);
        $this->deleteFromDisk($parent_delete);
        $this->domain->updateStatistics();
        $this->archive_prune->updateThreads();
        $this->overboard->removeThread($this);
        return true;
    }

    protected function deleteFromDatabase(bool $parent_delete): bool
    {
        if (empty($this->content_id->threadID())) {
            return false;
        }

        $prepared = $this->database->prepare(
            'DELETE FROM "' . $this->domain->reference('threads_table') . '" WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$this->content_id->threadID()]);
        return true;
    }

    protected function deleteFromDisk(bool $parent_delete): bool
    {
        $file_handler = nel_utilities()->fileHandler();
        $removed = false;

        if ($this->domain->reference('page_path') !== $this->pageFilePath()) {
            $removed = $file_handler->eraserGun($this->pageFilePath());
        }

        if ($this->domain->reference('src_path') !== $this->srcFilePath()) {
            $removed = $file_handler->eraserGun($this->srcFilePath());
        }

        if ($this->domain->reference('preview_path') !== $this->previewFilePath()) {
            $removed = $file_handler->eraserGun($this->srcFilePath());
        }

        return $removed;
    }

    public function verifyModifyPerms(): bool
    {
        return $this->firstPost()->verifyModifyPerms();
    }

    public function getParent(): Thread
    {
        return $this;
    }

    public function updateCounts()
    {
        $prepared = $this->database->prepare(
            'SELECT COUNT(*) FROM "' . $this->domain->reference('posts_table') . '" WHERE "parent_thread" = ?');
        $post_count = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
            PDO::FETCH_COLUMN);

        $this->changeData('post_count', $post_count);

        $prepared = $this->database->prepare(
            'SELECT COUNT(*) FROM "' . $this->domain->reference('posts_table') .
            '" WHERE "parent_thread" = ? AND "sage" = 0');
        $bump_count = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
            PDO::FETCH_COLUMN);

        $this->changeData('bump_count', $bump_count);

        $prepared = $this->database->prepare(
            'SELECT COUNT(*) FROM "' . $this->domain->reference('uploads_table') . '" WHERE "parent_thread" = ?');
        $total_uploads = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
            PDO::FETCH_COLUMN);

        $prepared = $this->database->prepare(
            'SELECT COUNT(*) FROM "' . $this->domain->reference('uploads_table') .
            '" WHERE "parent_thread" = ? AND "embed_url" IS NOT NULL');
        $embed_count = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
            PDO::FETCH_COLUMN);

        $file_count = $total_uploads - $embed_count;
        $this->changeData('total_uploads', $total_uploads);
        $this->changeData('file_count', $file_count);
        $this->changeData('embed_count', $embed_count);
        $this->writeToDatabase();
    }

    public function updateBumpTime(): void
    {
        if ($this->domain->setting('limit_bump_count') &&
            $this->data('bump_count') > $this->domain->setting('max_bumps')) {
            return;
        }

        $last_bump = $this->lastBumpPost();

        if (!$last_bump->exists()) {
            return;
        }

        if ($last_bump->data('post_time') === $this->data('bump_time')) {
            $last_bump_lower = $last_bump->data('post_time_milli') < $this->data('bump_time_milli');
        } else {
            $last_bump_lower = $last_bump->data('post_time') < $this->data('bump_time');
        }

        if (!$this->data('permasage') || $last_bump_lower) {
            $this->changeData('bump_time', $last_bump->data('post_time'));
            $this->changeData('bump_time_milli', $last_bump->data('post_time_milli'));
            $this->writeToDatabase();
        }
    }

    public function updateUpdateTime(): void
    {
        $last_post = $this->lastPost();

        if ($last_post->exists()) {
            $this->changeData('last_update_time', $last_post->data('post_time'));
            $this->changeData('last_update_time_milli', $last_post->data('post_time_milli'));
            $this->writeToDatabase();
        }
    }

    public function createDirectories(): bool
    {
        $file_handler = nel_utilities()->fileHandler();
        return $file_handler->createDirectory($this->pageFilePath());
    }

    public function toggleSticky(): bool
    {
        if (!$this->isLoaded()) {
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

    public function togglePermasage(): bool
    {
        $this->changeData('permasage', !$this->data('permasage'));
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
        $bump_limit = $this->domain->setting('max_bumps');

        if ($this->data('post_count') > $bump_limit) {
            $old_post_list = array_slice($descending_post_list, $bump_limit - 1);

            foreach ($old_post_list as $old_post) {
                if ($old_post['op'] == 1) {
                    continue;
                }

                $post_content_id = new ContentID(
                    ContentID::createIDString($this->content_id->threadID(), $old_post['post_number'], 0));
                $post = $post_content_id->getInstanceFromID($this->domain);
                $post->delete(true, true);
            }
        }
    }

    public function firstPost(): Post
    {
        $prepared = $this->database->prepare(
            'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
            '" WHERE "parent_thread" = ? AND "op" = 1');
        $post_id = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()], PDO::FETCH_COLUMN);
        $content_id = new ContentID(ContentID::createIDString($this->content_id->threadID(), $post_id, 0));
        $post = new Post($content_id, $this->domain);
        return $post;
    }

    public function lastPost(): Post
    {
        $prepared = $this->database->prepare(
            'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
            '" WHERE "parent_thread" = ? ORDER BY "post_number" DESC LIMIT 1');
        $post_id = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()], PDO::FETCH_COLUMN);
        $content_id = new ContentID(ContentID::createIDString($this->content_id->threadID(), $post_id, 0));
        $post = new Post($content_id, $this->domain);
        return $post;
    }

    public function lastBumpPost(): Post
    {
        $prepared = $this->database->prepare(
            'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
            '" WHERE "parent_thread" = ? AND "sage" = 0 ORDER BY "post_number" DESC LIMIT 1');
        $post_id = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()], PDO::FETCH_COLUMN);
        $content_id = new ContentID(ContentID::createIDString($this->content_id->threadID(), $post_id, 0));
        $post = new Post($content_id, $this->domain);
        return $post;
    }

    public function getNthPost(int $nth_post): Post
    {
        $prepared = $this->database->prepare(
            'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
            '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
        $post_list = $this->database->executePreparedFetchAll($prepared, [$this->content_id->threadID()],
            PDO::FETCH_COLUMN);
        $post_id = $post_list[$nth_post - 1] ?? 0;
        $content_id = new ContentID(ContentID::createIDString($this->content_id->threadID(), $post_id, 0));
        $post = new Post($content_id, $this->domain);
        return $post;
    }

    // Most of this is based on vichan's slugify
    public function generateSlug(Post $post): string
    {
        $slug = '';
        $max_length = $this->domain->setting('max_slug_length');

        if (!nel_true_empty($post->data('subject'))) {
            $base_text = $post->data('subject');
        } else if (!nel_true_empty($post->data('comment'))) {
            $base_text = $post->data('comment');
        } else {
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
        if (empty($matches)) {
            $slug = utf8_substr($slug, 0, $max_length);
        } else {
            $slug = $matches[1];
        }

        // Make lowercase
        $slug = strtolower($slug);

        return $slug;
    }

    public function pageBasename(): string
    {
        $page_filename = '';

        if ($this->domain->setting('slugify_thread_url') && !nel_true_empty($this->content_data['slug'])) {
            $page_filename = sprintf(nel_site_domain()->setting('slug_thread_filename_format'),
                $this->content_data['slug']);
        } else {
            $page_filename = sprintf(nel_site_domain()->setting('thread_filename_format'), $this->content_id->threadID());
        }

        return $page_filename;
    }

    public function getURL(bool $dynamic, string $query_string = ''): string
    {
        if ($dynamic) {
            return nel_build_router_url(
                [$this->domain->id(), $this->domain->reference('page_directory'), $this->content_id->threadID(),
                    $this->pageBasename()], $query_string === '', $query_string);
        }

        $base_path = $this->domain->reference('page_web_path') . $this->content_id->threadID() . '/';
        return $base_path . $this->pageBasename() . NEL_PAGE_EXT;
    }

    public function pageFilePath()
    {
        return $this->domain->reference('page_path') . $this->content_id->threadID() . '/';
    }

    public function srcFilePath()
    {
        return $this->domain->reference('src_path');
    }

    public function previewFilePath()
    {
        return $this->domain->reference('preview_path');
    }

    public function pageWebPath()
    {
        return $this->domain->reference('page_web_path') . $this->content_id->threadID() . '/';
    }

    public function srcWebPath()
    {
        return $this->domain->reference('src_web_path');
    }

    public function previewWebPath()
    {
        return $this->domain->reference('preview_web_path');
    }

    public function archive(bool $permanent): bool
    {
        return false; // TODO: Update for flattened structure
        $thread_data = $this->getJSON()->getJSON();
        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->domain->reference('archives_table') .
            '" ("thread_id", "thread_data", "time_archived", "permanent", "moar") VALUES (?, ?, ?, ?, ?)');
        $prepared->bindValue(1, $this->content_id->threadID(), PDO::PARAM_INT);
        $prepared->bindValue(2, $thread_data, PDO::PARAM_STR);
        $prepared->bindValue(3, time(), PDO::PARAM_INT);
        $prepared->bindValue(4, $permanent, PDO::PARAM_INT);
        $prepared->bindValue(5, $this->getMoar()->get(), PDO::PARAM_STR);
        $result = $this->database->executePrepared($prepared);

        if ($result !== true) {
            return false;
        }

        $file_handler = nel_utilities()->fileHandler();
        $file_handler->moveDirectory($this->domain->reference('src_path') . $this->content_id->threadID() . '/',
            $this->domain->reference('archive_src_path') . $this->content_id->threadID() . '/');
        $file_handler->moveDirectory($this->domain->reference('preview_path') . $this->content_id->threadID() . '/',
            $this->domain->reference('archive_preview_path') . $this->content_id->threadID() . '/');
        // TODO: regen as archive page
        $file_handler->moveDirectory($this->domain->reference('page_path') . $this->content_id->threadID() . '/',
            $this->domain->reference('archive_page_path') . $this->content_id->threadID() . '/');

        $this->deleteFromDatabase();
        return true;
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
        if (isset($this->content_data[$data_name])) {
            return $this->content_data[$data_name];
        }

        return $default;
    }

    public function data(string $key)
    {
        return $this->content_data[$key] ?? null;
    }

    public function transferData(array $new_data = null): array
    {
        if (!is_null($new_data)) {
            $this->content_data = $new_data;
        }

        return $this->content_data;
    }

    public function changeData(string $key, $new_data, bool $cast_null = true)
    {
        $column_types = $this->main_table->columnTypes();
        $type = $column_types[$key]['php_type'] ?? '';
        $new_data = nel_typecast($new_data, $type, $cast_null);
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

    public function getPosts(bool $ids_only = false): array
    {
        $posts = array();
        $prepared = $this->database->prepare(
            'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
            '" WHERE "parent_thread" = ? ORDER BY "post_time" ASC, "post_time_milli" ASC, "post_number" ASC');
        $post_list = $this->database->executePreparedFetchAll($prepared, [$this->content_id->threadID()],
            PDO::FETCH_COLUMN);

        if ($ids_only) {
            return $post_list;
        }

        foreach ($post_list as $id) {
            $content_id = new ContentID(ContentID::createIDString($this->content_id->threadID(), intval($id)));
            $posts[] = $content_id->getInstanceFromID($this->domain);
        }

        return $posts;
    }

    public function lastReplies(int $limit): array
    {
        $last_replies = array();
        $offset = $this->data('post_count') - $limit;

        if ($this->data('post_count') == 1) {
            return $last_replies;
        }

        if ($offset < 1) {
            $offset = 1;
        }

        $posts = array();
        $prepared = $this->database->prepare(
            'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
            '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC LIMIT ? OFFSET ?');
        $post_list = $this->database->executePreparedFetchAll($prepared,
            [$this->content_id->threadID(), $limit, $offset], PDO::FETCH_COLUMN);

        foreach ($post_list as $id) {
            $content_id = new ContentID(ContentID::createIDString($this->content_id->threadID(), intval($id)));
            $posts[] = $content_id->getInstanceFromID($this->domain);
        }

        return $posts;
    }

    public function getJSON(): ThreadJSON
    {
        return $this->json;
    }

    public function addPost(Post $post): void
    {
        if (!$this->isLoaded()) {
            $this->loadFromDatabase();
        }

        $fgsfds = new FGSFDS();
        $first_post = $this->firstPost();

        // If no first post, assume this is a new thread
        if (!$first_post->exists()) {
            $this->createDirectories();
            $this->changeData('thread_id', $post->contentID()->postID());
            $this->changeData('bump_time', $post->data('post_time'));
            $this->changeData('bump_time_milli', $post->data('post_time_milli'));
            $this->changeData('last_update', $post->data('post_time'));
            $this->changeData('last_update_milli', $post->data('post_time_milli'));
            $this->changeData('post_count', 1);
            $this->changeData('slug', $this->generateSlug($post));
            $this->changeData('salt', base64_encode(random_bytes(32)));
            $post->changeData('reply_to', 0);
            $post->changeData('op', true);
        } else {
            $this->changeData('last_update', $post->data('post_time'));
            $this->changeData('last_update_milli', $post->data('post_time_milli'));
            $this->changeData('post_count', $this->data('post_count') + 1);

            if ((!$this->domain->setting('limit_bump_count') ||
                ($this->data('bump_count') <= $this->domain->setting('max_bumps')) && !$fgsfds->commandIsSet('sage') &&
                !$this->data('permasage'))) {
                $this->changeData('bump_time', $post->data('post_time'));
                $this->changeData('bump_time_milli', $post->data('post_time_milli'));
            }

            $post->changeData('reply_to', $this->content_id->threadID());
            $post->changeData('op', false);
        }

        $this->writeToDatabase();
        $post->contentID()->changeThreadID($this->content_id->threadID());
        $post->changeData('parent_thread', $this->content_id->threadID());
        $post->writeToDatabase();
    }

    public function move(DomainBoard $domain, bool $keep_shadow): Thread
    {
        $cross_board = $domain->id() !== $this->domain->id();

        if (!$cross_board) {
            return $this;
        }

        $old_post_ids = $this->getPosts(true);
        $original_posts = $this->getPosts();
        $first_post = true;
        $new_thread = null;

        foreach ($original_posts as $post) {
            if ($first_post) {
                $new_thread = new Thread(new ContentID(), $domain);
                $new_thread->transferData($this->transferData());
                $post->move($new_thread, true);

                if ($keep_shadow) {
                    $this->changeData('shadow', true);
                    $this->getMoar()->modify('shadow_board_id', $domain->id());
                    $this->getMoar()->modify('shadow_thread_id', $new_thread->contentID()->threadID());
                    $this->getMoar()->modify('shadow_type', 'moved');
                    $this->changeData('locked', true);
                    $this->writeToDatabase();
                }

                $first_post = false;
            } else {
                $post->move($new_thread, false);
            }
        }

        $cites = new Cites($post->domain()->database());
        $new_post_ids = $new_thread->getPosts(true);
        $post_id_conversions = array();
        $post_count = count($old_post_ids);

        for ($i = 0; $i < $post_count; $i ++) {
            $post_id_conversions[$old_post_ids[$i]] = $new_post_ids[$i];
        }

        $cites->updateForMovedThread($this->domain(), $new_thread, $post_id_conversions);

        if (!$keep_shadow) {
            $this->delete(true, true);
        }

        $regen = new Regen();
        $regen->threads($domain, true, [$new_thread->contentID()->threadID()]);
        $regen->index($domain);
        $regen->index($this->domain);
        return $new_thread;
    }

    public function merge(Thread $incoming_thread, bool $keep_shadow): void
    {
        $cross_board = $incoming_thread->domain()->id() === $this->domain->id();
        $moved_incoming = $incoming_thread->move($this->domain, $keep_shadow);
        $moved_first = $moved_incoming->firstPost();
        $moved_first->changeData('op', false);
        $moved_first->writeToDatabase();
        $target_thread_id = $this->content_id->threadID();

        foreach ($moved_incoming->getPosts() as $incoming_post) {
            $incoming_post->changeData('parent_thread', $target_thread_id);
            $incoming_post->writeToDatabase();

            foreach ($incoming_post->getUploads() as $incoming_upload) {
                $incoming_upload->changeData('parent_thread', $target_thread_id);
                $incoming_upload->writeToDatabase();
            }
        }

        $this->updateBumpTime();
        $this->updateUpdateTime();
        $this->updateCounts();

        if ($cross_board && $keep_shadow) {
            $incoming_thread->changeData('shadow', true);
            $incoming_thread->getMoar()->modify('shadow_board_id', $this->domain->id());
            $incoming_thread->getMoar()->modify('shadow_thread_id', $this->contentID()->threadID());
            $incoming_thread->getMoar()->modify('shadow_type', 'merged');
            $incoming_thread->changeData('locked', true);
            $incoming_thread->writeToDatabase();
        } else {
            $incoming_thread->delete(true, true);
        }
    }
}