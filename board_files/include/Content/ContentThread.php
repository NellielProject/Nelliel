<?php

namespace Nelliel\Content;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ContentThread extends ContentHandler
{

    function __construct($database, $content_id, $domain, $db_load = false)
    {
        $this->database = $database;
        $this->content_id = $content_id;
        $this->domain = $domain;

        if ($db_load)
        {
            $this->loadFromDatabase();
        }
    }

    public function loadFromDatabase($temp_database = null)
    {
        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare(
                'SELECT * FROM "' . $this->domain->reference('threads_table') . '" WHERE "thread_id" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->content_id->thread_id], PDO::FETCH_ASSOC);

        if (empty($result))
        {
            return false;
        }

        $this->content_data = $result;
        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (empty($this->content_data) || empty($this->content_id->thread_id))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare(
                'SELECT "thread_id" FROM "' . $this->domain->reference('threads_table') . '" WHERE "thread_id" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->content_id->thread_id], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $database->prepare(
                    'UPDATE "' . $this->domain->reference('threads_table') . '" SET "first_post" = :first_post,
                    "last_post" = :last_post, "last_bump_time" = :last_bump_time, "last_bump_time_milli" = :last_bump_time_milli,
                    "total_files" = :total_files, "last_update" = :last_update, "last_update_milli" = :last_update_milli, "post_count" = :post_count,
                    "thread_sage" = :thread_sage, "sticky" = :sticky, "archive_status" = :archive_status,
                    "locked" = :locked WHERE "thread_id" = :thread_id');
        }
        else
        {
            $prepared = $database->prepare(
                    'INSERT INTO "' . $this->domain->reference('threads_table') . '" ("thread_id", "first_post", "last_post",
                    "last_bump_time", "last_bump_time_milli", "total_files", "last_update", "last_update_milli",
                    "post_count", "thread_sage", "sticky", "archive_status", "locked") VALUES
                    (:thread_id, :first_post, :last_post, :last_bump_time, :last_bump_time_milli, :total_files,
                    :last_update, :last_update_milli, :post_count, :thread_sage, :sticky, :archive_status, :locked)');
        }

        $prepared->bindValue(':thread_id', $this->content_id->thread_id, PDO::PARAM_INT);
        $prepared->bindValue(':first_post', $this->contentDataOrDefault('first_post', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_post', $this->contentDataOrDefault('last_post', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_bump_time', $this->contentDataOrDefault('last_bump_time', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_bump_time_milli', $this->contentDataOrDefault('last_bump_time_milli', 0),
                PDO::PARAM_INT);
        $prepared->bindValue(':total_files', $this->contentDataOrDefault('total_files', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_update', $this->contentDataOrDefault('last_update', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_update_milli', $this->contentDataOrDefault('last_update_milli', 0), PDO::PARAM_INT);
        $prepared->bindValue(':post_count', $this->contentDataOrDefault('post_count', 0), PDO::PARAM_INT);
        $prepared->bindValue(':thread_sage', $this->contentDataOrDefault('thread_sage', 0), PDO::PARAM_INT);
        $prepared->bindValue(':sticky', $this->contentDataOrDefault('sticky', 0), PDO::PARAM_INT);
        $prepared->bindValue(':archive_status', $this->contentDataOrDefault('archive_status', 0), PDO::PARAM_INT);
        $prepared->bindValue(':locked', $this->contentDataOrDefault('locked', 0), PDO::PARAM_INT);
        $database->executePrepared($prepared);
        return true;
    }

    public function createDirectories()
    {
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->createDirectory($this->domain->reference('src_path') . $this->content_id->thread_id,
                DIRECTORY_PERM);
        $file_handler->createDirectory($this->domain->reference('preview_path') . $this->content_id->thread_id,
                DIRECTORY_PERM);
        $file_handler->createDirectory($this->domain->reference('page_path') . $this->content_id->thread_id,
                DIRECTORY_PERM);
    }

    public function remove($perm_override = false)
    {
        if (!$perm_override && !$this->verifyModifyPerms())
        {
            return false;
        }

        if (!$perm_override && $this->domain->reference('locked'))
        {
            nel_derp(53, _gettext('Cannot remove thread. Board is locked.'));
        }

        $this->removeFromDatabase();
        $this->removeFromDisk();
    }

    protected function removeFromDatabase($temp_database = null)
    {
        if (empty($this->content_id->thread_id))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare(
                'DELETE FROM "' . $this->domain->reference('threads_table') . '" WHERE "thread_id" = ?');
        $database->executePrepared($prepared, [$this->content_id->thread_id]);
        $prepared = $database->prepare(
                'DELETE FROM "' . CITES_TABLE . '" WHERE "source_thread" = ? OR "target_thread" = ?');
        $database->executePrepared($prepared, [$this->content_id->thread_id, $this->content_id->thread_id]);
        return true;
    }

    protected function removeFromDisk()
    {
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->eraserGun($this->domain->reference('src_path') . $this->content_id->thread_id);
        $file_handler->eraserGun($this->domain->reference('preview_path') . $this->content_id->thread_id);
        $file_handler->eraserGun($this->domain->reference('page_path') . $this->content_id->thread_id);
    }

    public function updateCounts()
    {
        $prepared = $this->database->prepare(
                'SELECT COUNT("post_number") FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "parent_thread" = ?');
        $post_count = $this->database->executePreparedFetch($prepared, [$this->content_id->thread_id],
                PDO::FETCH_COLUMN);

        $prepared = $this->database->prepare(
                'UPDATE "' . $this->domain->reference('threads_table') . '" SET "post_count" = ? WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$post_count, $this->content_id->thread_id]);

        $prepared = $this->database->prepare(
                'SELECT COUNT("entry") FROM "' . $this->domain->reference('content_table') .
                '" WHERE "parent_thread" = ?');
        $file_count = $this->database->executePreparedFetch($prepared, [$this->content_id->thread_id],
                PDO::FETCH_COLUMN);

        $prepared = $this->database->prepare(
                'UPDATE "' . $this->domain->reference('threads_table') . '" SET "total_files" = ? WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$file_count, $this->content_id->thread_id]);

        $first_post = $this->firstPost();
        $last_post = $this->lastPost();
        $prepared = $this->database->prepare(
                'UPDATE "' . $this->domain->reference('threads_table') .
                '" SET "first_post" = ?, "last_post" = ? WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$first_post, $last_post, $this->content_id->thread_id]);
    }

    public function verifyModifyPerms()
    {
        $post = new ContentPost($this->database, $this->content_id, $this->domain);
        $post->content_id->post_id = $this->firstPost();
        return $post->verifyModifyPerms();
    }

    public function sticky()
    {
        $session = new \Nelliel\Session(new \Nelliel\Auth\Authorization($this->database), true);
        $user = $session->sessionUser();

        if (!$user->boardPerm($this->domain->id(), 'perm_post_sticky'))
        {
            nel_derp(400, _gettext('You are not allowed to sticky or unsticky threads.'));
        }

        if (!$this->dataIsLoaded(true))
        {
            return false;
        }

        $this->content_data['sticky'] = ($this->content_data['sticky'] == 1) ? 0 : 1;
        $this->writeToDatabase();
        return true;
    }

    public function lock()
    {
        $session = new \Nelliel\Session(new \Nelliel\Auth\Authorization($this->database), true);
        $user = $session->sessionUser();

        if (!$user->boardPerm($this->domain->id(), 'perm_post_lock'))
        {
            nel_derp(401, _gettext('You are not allowed to lock or unlock threads.'));
        }

        if (!$this->dataIsLoaded(true))
        {
            return false;
        }

        $this->content_data['locked'] = ($this->content_data['locked'] == 1) ? 0 : 1;
        $this->writeToDatabase();
        return true;
    }

    public function lastPost($no_sage = false)
    {
        if ($no_sage)
        {
            $prepared = $this->database->prepare(
                    'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
                    '" WHERE "parent_thread" = ? AND "sage" = 0 ORDER BY "post_number" DESC');
        }
        else
        {
            $prepared = $this->database->prepare(
                    'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
                    '" WHERE "parent_thread" = ? ORDER BY "post_number" DESC');
        }

        return $this->database->executePreparedFetch($prepared, [$this->content_id->thread_id], PDO::FETCH_COLUMN);
    }

    public function firstPost()
    {
        $prepared = $this->database->prepare(
                'SELECT "post_number" FROM "' . $this->domain->reference('posts_table') .
                '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
        return $this->database->executePreparedFetch($prepared, [$this->content_id->thread_id], PDO::FETCH_COLUMN);
    }
}