<?php
declare(strict_types = 1);

namespace Nelliel\Content;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\ArchiveAndPrune;
use Nelliel\Cites;
use Nelliel\Moar;
use Nelliel\API\JSON\Nelliel\PostJSON;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPost;
use Nelliel\Tables\TablePosts;
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
        $this->main_table = new TablePosts($this->database, nel_utilities()->sqlCompatibility());
        $this->main_table->tableName($domain->reference('posts_table'));
        $this->json = new PostJSON($this);
        $this->sql_helpers = nel_utilities()->sqlHelpers();

        if ($load) {
            $this->loadFromDatabase(true);
        }

        $this->archive_prune = new ArchiveAndPrune($this->domain, nel_utilities()->fileHandler());
    }

    public function exists(): bool
    {
        return $this->loadFromDatabase(false);
    }

    public function loadFromDatabase(bool $populate = true): bool
    {
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . $this->domain->reference('posts_table') . '" WHERE "post_number" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()], PDO::FETCH_ASSOC);

        if (empty($result)) {
            return false;
        }

        if (!$populate) {
            return true;
        }

        $result['ip_address'] = nel_convert_ip_from_storage($result['ip_address']);
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
        if (!$this->isLoaded() || empty($this->content_id->postID())) {
            return false;
        }

        $filtered_data = $this->main_table->filterColumns($this->content_data);
        $filtered_data['ip_address'] = nel_prepare_ip_for_storage($this->data('ip_address'));
        $filtered_data['moar'] = $this->getMoar()->getJSON();
        $pdo_types = $this->main_table->getPDOTypes($filtered_data);
        $column_list = array_keys($filtered_data);
        $values = array_values($filtered_data);
        $row_check_data = ['post_number' => $this->content_id->postID()];

        if ($this->main_table->rowExists($row_check_data)) {
            $where_columns = ['post_number'];
            $where_keys = ['where_post_number'];
            $where_values = [$this->content_id->postID()];
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
                nel_derp(62, _gettext('Cannot remove post. Board is locked.'));
            }

            $session = new Session();
            $user = $session->user();
            $bypass = false;

            if ($user && $session->user()->checkPermission($this->domain, 'perm_bypass_renzoku')) {
                $bypass = true;
            }

            $delete_post_renzoku = $this->domain->setting('delete_post_renzoku');

            if (!$bypass && $delete_post_renzoku > 0 && time() - $this->content_data['post_time'] < $delete_post_renzoku) {
                nel_derp(64,
                    sprintf(_gettext('You must wait %d seconds after making a post before it can be deleted.'),
                        $delete_post_renzoku));
            }
        }

        // Threads can have just OP deleted but right now we don't use that.
        if ($this->data('op') && !$parent_delete) {
            return $this->getParent()->delete($perm_override);
        }

        $uploads = $this->getUploads();

        foreach ($uploads as $upload) {
            $upload->delete(true, true);
        }

        $this->deleteFromDatabase($parent_delete);
        $this->deleteFromDisk($parent_delete);

        if (!$parent_delete) {
            $parent_thread = $this->getParent();
            $parent_thread->updateCounts();
            $parent_thread->updateBumpTime();
            $parent_thread->updateUpdateTime();
            $this->archive_prune->updateThreads();
        }

        return true;
    }

    protected function deleteFromDatabase(bool $parent_delete): bool
    {
        if (empty($this->content_id->postID()) || $parent_delete) {
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

    protected function deleteFromDisk(bool $parent_delete): bool
    {
        $file_handler = nel_utilities()->fileHandler();
        $parent = $this->getParent();
        $removed = false;

        if ($parent->srcFilePath() !== $this->srcFilePath()) {
            $removed = $file_handler->eraserGun($this->srcFilePath());
        }

        if ($parent->previewFilePath() !== $this->previewFilePath()) {
            $removed = $file_handler->eraserGun($this->previewFilePath());
        }

        return $removed;
    }

    public function verifyModifyPerms(): bool
    {
        $session = new Session();
        $user = $session->user();

        if (empty($this->content_data)) {
            $this->loadFromDatabase();
        }

        $flag = false;

        if ($session->isActive() && $user->checkPermission($this->domain, 'perm_delete_content')) {
            if (!nel_true_empty($this->data('username'))) {
                $mod_post_user = $this->authorization->getUser($this->data('username') ?? '');

                $flag = $this->authorization->roleLevelCheck($user->getDomainRole($this->domain)->id(),
                    $mod_post_user->getDomainRole($this->domain)->id());
            } else {
                $flag = true;
            }
        }

        $update_sekrit = $_POST['update_sekrit'] ?? '';

        if (!$flag && $this->domain->setting('user_delete_own')) {
            if (!nel_true_empty($this->data('password'))) {
                $flag = hash_equals($this->content_data['password'], nel_post_password_hash($update_sekrit));
            }

            if (!$flag && $this->domain->setting('allow_op_thread_moderation')) {
                $flag = hash_equals($this->getParent()->firstPost()->data('password'),
                    nel_post_password_hash($update_sekrit));
            }
        }

        if (!$flag) {
            nel_derp(60, _gettext('Password is wrong or you are not allowed to delete that.'));
        }

        return true;
    }

    public function getParent(): Thread
    {
        if (is_null($this->parent)) {
            $content_id = new ContentID();
            $content_id->changeThreadID($this->content_id->threadID());
            $this->parent = new Thread($content_id, $this->domain);
        }

        return $this->parent;
    }

    // TODO: See if we can move this step to Thread or eliminate it entirely
    public function reserveDatabaseRow(): bool
    {
        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->domain->reference('posts_table') .
            '" ("post_time", "post_time_milli", "hashed_ip_address", "visitor_id") VALUES (?, ?, ?, ?)');
        $success = $this->database->executePrepared($prepared,
            [$this->data('post_time'), $this->data('post_time_milli'), $this->data('hashed_ip_address'),
                $this->data('visitor_id')]);

        if (!$success) {
            return false;
        }

        $prepared = $this->database->prepare(
            'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
            '" WHERE "post_time" = ? AND "post_time_milli" = ? AND "hashed_ip_address" = ? AND "visitor_id" = ?');
        $result = $this->database->executePreparedFetch($prepared,
            [$this->data('post_time'), $this->data('post_time_milli'), $this->data('hashed_ip_address'),
                $this->data('visitor_id')], PDO::FETCH_COLUMN, true);
        $this->content_id->changeThreadID(
            ($this->content_id->threadID() === 0) ? $result : $this->content_id->threadID());
        $this->changeData('post_number', $result);
        $this->changeData('parent_thread', $this->content_id->threadID());
        $this->content_id->changePostID($result);
        return true;
    }

    public function updateCounts()
    {
        $prepared = $this->database->prepare(
            'SELECT COUNT(*) FROM "' . $this->domain->reference('uploads_table') . '" WHERE "post_ref" = ?');
        $total_uploads = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()],
            PDO::FETCH_COLUMN, true);

        $prepared = $this->database->prepare(
            'SELECT COUNT(*) FROM "' . $this->domain->reference('uploads_table') .
            '" WHERE "post_ref" = ? AND "embed_url" IS NULL');
        $file_count = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()], PDO::FETCH_COLUMN,
            true);

        $prepared = $this->database->prepare(
            'SELECT COUNT(*) FROM "' . $this->domain->reference('uploads_table') .
            '" WHERE "post_ref" = ? AND "embed_url" IS NOT NULL');
        $embed_count = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()],
            PDO::FETCH_COLUMN, true);

        $prepared = $this->database->prepare(
            'UPDATE "' . $this->domain->reference('posts_table') .
            '" SET "total_uploads" = ?, "file_count" = ?, "embed_count" = ? WHERE "post_number" = ?');
        $this->database->executePrepared($prepared,
            [$total_uploads, $file_count, $embed_count, $this->content_id->postID()]);
    }

    public function convertToThread(bool $preserve_time = false): Thread
    {
        $original_src_path = $this->srcFilePath();
        $original_preview_path = $this->previewFilePath();
        $new_content_id = new ContentID();
        $new_content_id->changeThreadID($this->content_id->postID());
        $new_content_id->changePostID($this->content_id->postID());
        $new_thread = new Thread($new_content_id, $this->domain);
        $new_thread->changeData('thread_id', $this->content_id->postID());

        if ($preserve_time) {
            $new_thread->changeData('bump_time', $this->data('post_time'));
            $new_thread->changeData('bump_time_milli', $this->data('post_time_milli'));
            $new_thread->changeData('last_update', $this->data('post_time'));
            $new_thread->changeData('last_update_milli', $this->data('post_time_milli'));
        } else {
            $time = nel_get_microtime();
            $new_thread->changeData('bump_time', $time['time']);
            $new_thread->changeData('bump_time_milli', $time['milli']);
            $new_thread->changeData('last_update', $time['time']);
            $new_thread->changeData('last_update_milli', $time['milli']);
        }

        $new_thread->writeToDatabase();
        $new_thread->loadFromDatabase();
        $new_thread->createDirectories();
        $uploads = $this->getUploads();

        foreach ($uploads as $upload) {
            $upload->changeData('parent_thread', $new_thread->contentID()->threadID());
            $upload->writeToDatabase();
        }

        $this->loadFromDatabase();
        $new_thread->addPost($this);
        $this->parent = $new_thread;
        $this->writeToDatabase();
        $this->createDirectories();
        $file_handler = nel_utilities()->fileHandler();
        $file_handler->moveDirectory($original_src_path, $this->srcFilePath());
        $file_handler->moveDirectory($original_preview_path, $this->previewFilePath());
        $new_thread->updateCounts();
        $this->getParent()->updateBumpTime();
        $this->getParent()->updateCounts();
        $this->getParent()->updateUpdateTime();
        $this->archive_prune->updateThreads();
        return $new_thread;
    }

    public function createDirectories()
    {
        $file_handler = nel_utilities()->fileHandler();
        $file_handler->createDirectory($this->srcFilePath());
        $file_handler->createDirectory($this->previewFilePath());
    }

    public function getCache(): array
    {
        $prepared = $this->database->prepare(
            'SELECT "cache" FROM "' . $this->domain->reference('posts_table') . '" WHERE "post_number" = ?');
        $cache = $this->database->executePreparedFetch($prepared, [$this->content_id->postID()], PDO::FETCH_COLUMN);

        if (is_string($cache)) {
            return json_decode($cache, true);
        }

        return array();
    }

    public function storeCache(): void
    {
        $cache_array = array();
        $output_post = new OutputPost($this->domain, false);
        $cache_array['comment_markup'] = $output_post->parseComment($this->data('comment'), $this);
        $cache_array['backlink_data'] = $output_post->generateBacklinks($this);
        $encoded_cache = json_encode($cache_array, JSON_UNESCAPED_UNICODE);
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->domain->reference('posts_table') .
            '" SET "cache" = ?, "regen_cache" = 0 WHERE "post_number" = ?');
        $this->database->executePrepared($prepared, [$encoded_cache, $this->content_id->postID()]);
    }

    public function srcFilePath(): string
    {
        return $this->domain->reference('src_path');
    }

    public function previewFilePath(): string
    {
        return $this->domain->reference('preview_path');
    }

    public function srcWebPath(): string
    {
        return $this->domain->reference('src_web_path');
    }

    public function previewWebPath(): string
    {
        return $this->domain->reference('preview_web_path');
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

    public function getURL(bool $dynamic): string
    {
        $parent = $this->getParent();
        $thread_url = $parent->getURL($dynamic);
        return $thread_url . '#t' . $parent->contentID()->threadID() . 'p' . $this->content_id->postID();
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

    public function getLastUploadOrder(): int
    {
        $last_order = 0;
        $prepared = $this->database->prepare(
            'SELECT "upload_order" FROM "' . $this->domain->reference('uploads_table') .
            '" WHERE "post_ref" = ? ORDER BY "upload_order" ASC');
        $upload_orders = $this->database->executePreparedFetchAll($prepared, [$this->contentID()->postID()],
            PDO::FETCH_COLUMN);

        foreach ($upload_orders as $upload_order) {
            if ($upload_order > $last_order) {
                $last_order = (int) $upload_order;
            }
        }

        return $last_order;
    }

    public function getUploads(): array
    {
        $uploads = array();
        $prepared = $this->database->prepare(
            'SELECT "upload_order" FROM "' . $this->domain->reference('uploads_table') .
            '" WHERE "post_ref" = ? ORDER BY "upload_order" ASC');
        $upload_list = $this->database->executePreparedFetchAll($prepared, [$this->contentID()->postID()],
            PDO::FETCH_COLUMN);

        foreach ($upload_list as $id) {
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

    public function move(Thread $new_thread, bool $is_shadow): Post
    {
        $new_board = $new_thread->domain()->id() !== $this->domain()->id();

        if ($is_shadow) {
            $this->changeData('shadow', true);
            $this->writeToDatabase();
        }

        if ($new_board) {
            $new_post = new Post(new ContentID(), $new_thread->domain());
            $new_post->transferData($this->transferData());
            $new_post->storeMoar($this->content_moar);
            $new_post->reserveDatabaseRow();

            // If this is OP and we're moving the whole thread, finish preparation before continuing
            if ($this->data('op')) {
                $new_thread->contentID()->changeThreadID($new_post->contentID()->postID());
                $new_thread->changedata('thread_id', $new_thread->contentID()->threadID());
                $new_thread->writeToDatabase();
                $new_thread->createDirectories();
            }
        } else {
            $new_post = $this;
        }

        $new_thread->addPost($new_post);
        $new_post->writeToDatabase();

        foreach ($this->getUploads() as $upload) {
            $upload->move($new_post, $is_shadow);
        }

        if ($new_board && !$is_shadow) {
            $this->delete(true, false);
        }

        return $new_post;
    }
}