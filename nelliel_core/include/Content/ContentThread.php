<?php

namespace Nelliel\Content;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Cites;
use Nelliel\Domain;
use PDO;
use Nelliel\ArchiveAndPrune;

class ContentThread extends ContentHandler
{
    protected $threads_table;
    protected $posts_table;
    protected $content_table;
    protected $src_path;
    protected $preview_path;
    protected $page_path;
    protected $archived;
    protected $archive_prune;

    function __construct(ContentID $content_id, Domain $domain, bool $archived = false)
    {
        $this->database = $domain->database();
        $this->content_id = $content_id;
        $this->domain = $domain;
        $this->archived = $archived;
        $this->threads_table = $this->domain->reference('threads_table');
        $this->posts_table = $this->domain->reference('posts_table');
        $this->content_table = $this->domain->reference('content_table');

        if ($archived)
        {
            $this->src_path = $this->domain->reference('archive_src_path');
            $this->preview_path = $this->domain->reference('archive_preview_path');
            $this->page_path = $this->domain->reference('archive_page_path');
        }
        else
        {

            $this->src_path = $this->domain->reference('src_path');
            $this->preview_path = $this->domain->reference('preview_path');
            $this->page_path = $this->domain->reference('page_path');
        }

        $this->archive_prune = new \Nelliel\ArchiveAndPrune($this->domain, new \Nelliel\Utility\FileHandler());
        $this->storeMeta(new Meta());
    }

    public function loadFromDatabase()
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . $this->threads_table . '" WHERE "thread_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()], PDO::FETCH_ASSOC);

        if (empty($result))
        {
            return false;
        }

        $this->content_data = $result;
        $meta = $result['meta'] ?? '';
        $this->getMeta()->storeFromJSON($meta);
        return true;
    }

    public function writeToDatabase()
    {
        if (empty($this->content_data) || empty($this->content_id->threadID()))
        {
            return false;
        }

        $prepared = $this->database->prepare(
                'SELECT "thread_id" FROM "' . $this->threads_table . '" WHERE "thread_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . $this->threads_table .
                    '" SET "first_post" = :first_post,
                    "last_post" = :last_post, "last_bump_time" = :last_bump_time, "last_bump_time_milli" = :last_bump_time_milli,
                    "content_count" = :content_count, "last_update" = :last_update, "last_update_milli" = :last_update_milli, "post_count" = :post_count,
                    "thread_sage" = :thread_sage, "sticky" = :sticky, "archive_status" = :archive_status,
                    "locked" = :locked WHERE "thread_id" = :thread_id');
        }
        else
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . $this->threads_table .
                    '" ("thread_id", "first_post", "last_post",
                    "last_bump_time", "last_bump_time_milli", "content_count", "last_update", "last_update_milli",
                    "post_count", "thread_sage", "sticky", "archive_status", "locked") VALUES
                    (:thread_id, :first_post, :last_post, :last_bump_time, :last_bump_time_milli, :content_count,
                    :last_update, :last_update_milli, :post_count, :thread_sage, :sticky, :archive_status, :locked)');
        }

        $prepared->bindValue(':thread_id', $this->content_id->threadID(), PDO::PARAM_INT);
        $prepared->bindValue(':first_post', $this->contentDataOrDefault('first_post', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_post', $this->contentDataOrDefault('last_post', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_bump_time', $this->contentDataOrDefault('last_bump_time', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_bump_time_milli', $this->contentDataOrDefault('last_bump_time_milli', 0),
                PDO::PARAM_INT);
        $prepared->bindValue(':content_count', $this->contentDataOrDefault('content_count', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_update', $this->contentDataOrDefault('last_update', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_update_milli', $this->contentDataOrDefault('last_update_milli', 0), PDO::PARAM_INT);
        $prepared->bindValue(':post_count', $this->contentDataOrDefault('post_count', 0), PDO::PARAM_INT);
        $prepared->bindValue(':thread_sage', $this->contentDataOrDefault('thread_sage', 0), PDO::PARAM_INT);
        $prepared->bindValue(':sticky', $this->contentDataOrDefault('sticky', 0), PDO::PARAM_INT);
        $prepared->bindValue(':archive_status', $this->contentDataOrDefault('archive_status', 0), PDO::PARAM_INT);
        $prepared->bindValue(':locked', $this->contentDataOrDefault('locked', 0), PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
        $this->archive_prune->updateThreads();
        return true;
    }

    public function createDirectories()
    {
        $file_handler = new \Nelliel\Utility\FileHandler();
        $file_handler->createDirectory($this->src_path . $this->content_id->threadID(), NEL_DIRECTORY_PERM);
        $file_handler->createDirectory($this->preview_path . $this->content_id->threadID(), NEL_DIRECTORY_PERM);
        $file_handler->createDirectory($this->page_path . $this->content_id->threadID(), NEL_DIRECTORY_PERM);
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
                nel_derp(53, _gettext('Cannot remove thread. Board is locked.'));
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

        $prepared = $this->database->prepare('DELETE FROM "' . $this->threads_table . '" WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$this->content_id->threadID()]);
        $cites = new Cites($this->database);
        $cites->removeForThread($this->domain, $this->content_id);
        return true;
    }

    protected function removeFromDisk()
    {
        $file_handler = new \Nelliel\Utility\FileHandler();
        $file_handler->eraserGun($this->src_path . $this->content_id->threadID());
        $file_handler->eraserGun($this->preview_path . $this->content_id->threadID());
        $file_handler->eraserGun($this->page_path . $this->content_id->threadID());
    }

    public function postCount()
    {
        $prepared = $this->database->prepare(
                'SELECT COUNT("post_number") FROM "' . $this->posts_table . '" WHERE "parent_thread" = ?');
        $post_count = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);
        return $post_count;
    }

    public function updateCounts()
    {
        $post_count = $this->postCount();

        $prepared = $this->database->prepare(
                'UPDATE "' . $this->threads_table . '" SET "post_count" = ? WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$post_count, $this->content_id->threadID()]);

        $prepared = $this->database->prepare(
                'SELECT COUNT("entry") FROM "' . $this->content_table . '" WHERE "parent_thread" = ?');
        $content_count = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);

        $prepared = $this->database->prepare(
                'UPDATE "' . $this->threads_table . '" SET "content_count" = ? WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$content_count, $this->content_id->threadID()]);

        $first_post = $this->firstPost();
        $last_post = $this->lastPost();
        $prepared = $this->database->prepare(
                'UPDATE "' . $this->threads_table . '" SET "first_post" = ?, "last_post" = ? WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$first_post, $last_post, $this->content_id->threadID()]);
    }

    protected function verifyModifyPerms()
    {
        $post = new ContentPost($this->content_id, $this->domain, $this->archived);
        $post->content_id->changePostID($this->firstPost());
        return $post->verifyModifyPerms();
    }

    public function sticky()
    {
        if (!$this->dataLoaded(true))
        {
            return false;
        }

        $this->content_data['sticky'] = 1;
        $success = $this->writeToDatabase();
        $this->archive_prune->updateThreads();
        return $success;
    }

    public function unsticky()
    {
        if (!$this->dataLoaded(true))
        {
            return false;
        }

        $this->content_data['sticky'] = 0;
        $this->archive_prune->updateThreads();
        return $success;
    }

    public function lock()
    {
        if (!$this->dataLoaded(true))
        {
            return false;
        }

        $this->content_data['locked'] = 1;
        return $this->writeToDatabase();
    }

    public function unlock()
    {
        if (!$this->dataLoaded(true))
        {
            return false;
        }

        $this->content_data['locked'] = 0;
        return $this->writeToDatabase();
    }

    public function lastPost(bool $no_sage = false)
    {
        if ($no_sage)
        {
            $prepared = $this->database->prepare(
                    'SELECT "post_number" FROM "' . $this->posts_table .
                    '" WHERE "parent_thread" = ? AND "sage" = 0 ORDER BY "post_number" DESC');
        }
        else
        {
            $prepared = $this->database->prepare(
                    'SELECT "post_number" FROM "' . $this->posts_table .
                    '" WHERE "parent_thread" = ? ORDER BY "post_number" DESC');
        }

        $last_post = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);
        return intval($last_post);
    }

    public function firstPost()
    {
        $prepared = $this->database->prepare(
                'SELECT "post_number" FROM "' . $this->posts_table .
                '" WHERE "parent_thread" = ? ORDER BY "post_number" ASC');
        $first_post = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);
        return intval($first_post);
    }

    public function isArchived()
    {
        return $this->archived;
    }
}