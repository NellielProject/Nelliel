<?php

namespace Nelliel\Content;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Cites;
use Nelliel\Domains\Domain;
use Nelliel\Moar;
use Nelliel\Overboard;
use PDO;

class ContentThread extends ContentHandler
{
    protected $threads_table;
    protected $posts_table;
    protected $content_table;
    protected $src_path;
    protected $preview_path;
    protected $page_path;
    protected $archive_prune;
    protected $overboard;

    function __construct(ContentID $content_id, Domain $domain)
    {
        $this->database = $domain->database();
        $this->content_id = $content_id;
        $this->domain = $domain;
        $this->threads_table = $this->domain->reference('threads_table');
        $this->posts_table = $this->domain->reference('posts_table');
        $this->content_table = $this->domain->reference('content_table');
        $this->src_path = $this->domain->reference('src_path');
        $this->preview_path = $this->domain->reference('preview_path');
        $this->page_path = $this->domain->reference('page_path');
        $this->archive_prune = new \Nelliel\ArchiveAndPrune($this->domain, nel_utilities()->fileHandler());
        $this->storeMoar(new Moar());
        $this->overboard = new Overboard($this->database);
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
        $moar = $result['moar'] ?? '';
        $this->getMoar()->storeFromJSON($moar);
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
                    '" SET "last_bump_time" = :last_bump_time, "last_bump_time_milli" = :last_bump_time_milli,
                    "content_count" = :content_count, "last_update" = :last_update, "last_update_milli" = :last_update_milli,
                    "post_count" = :post_count, "permasage" = :permasage, "sticky" = :sticky, "cyclic" = :cyclic,
                    "archive_status" = :archive_status, "locked" = :locked WHERE "thread_id" = :thread_id');
        }
        else
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . $this->threads_table .
                    '" ("thread_id", "last_bump_time", "last_bump_time_milli", "content_count", "last_update",
                    "last_update_milli", "post_count", "permasage", "sticky", "archive_status", "locked")
                    VALUES (:thread_id, :last_bump_time, :last_bump_time_milli, :content_count, :last_update,
                    :last_update_milli, :post_count, :permasage, :sticky, :cyclic, :archive_status, :locked)');
        }

        $prepared->bindValue(':thread_id', $this->content_id->threadID(), PDO::PARAM_INT);
        $prepared->bindValue(':last_bump_time', $this->contentDataOrDefault('last_bump_time', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_bump_time_milli', $this->contentDataOrDefault('last_bump_time_milli', 0),
                PDO::PARAM_INT);
        $prepared->bindValue(':content_count', $this->contentDataOrDefault('content_count', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_update', $this->contentDataOrDefault('last_update', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_update_milli', $this->contentDataOrDefault('last_update_milli', 0), PDO::PARAM_INT);
        $prepared->bindValue(':post_count', $this->contentDataOrDefault('post_count', 0), PDO::PARAM_INT);
        $prepared->bindValue(':permasage', $this->contentDataOrDefault('permasage', 0), PDO::PARAM_INT);
        $prepared->bindValue(':sticky', $this->contentDataOrDefault('sticky', 0), PDO::PARAM_INT);
        $prepared->bindValue(':cyclic', $this->contentDataOrDefault('cyclic', 0), PDO::PARAM_INT);
        $prepared->bindValue(':archive_status', $this->contentDataOrDefault('archive_status', 0), PDO::PARAM_INT);
        $prepared->bindValue(':locked', $this->contentDataOrDefault('locked', 0), PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
        $this->archive_prune->updateThreads();
        $this->overboard->updateThread($this);
        return true;
    }

    public function createDirectories()
    {
        $file_handler = nel_utilities()->fileHandler();
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

        $prepared = $this->database->prepare('DELETE FROM "' . $this->threads_table . '" WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$this->content_id->threadID()]);
        $cites = new Cites($this->database);
        $cites->updateForThread($this);
        $cites->removeForThread($this);
        return true;
    }

    protected function removeFromDisk()
    {
        $file_handler = nel_utilities()->fileHandler();
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
    }

    protected function verifyModifyPerms()
    {
        $post = new ContentPost($this->content_id, $this->domain);
        $post->content_id->changePostID($this->firstPost());
        return $post->verifyModifyPerms();
    }

    public function getParent()
    {
        return $this;
    }

    public function sticky(): bool
    {
        if (!$this->dataLoaded(true))
        {
            return false;
        }

        $this->content_data['sticky'] = ($this->content_data['sticky'] == 0) ? 1 : 0;
        ;
        $success = $this->writeToDatabase();
        $this->archive_prune->updateThreads();
        return $success;
    }

    public function lock(): bool
    {
        if (!$this->dataLoaded(true))
        {
            return false;
        }

        $this->content_data['locked'] = ($this->content_data['locked'] == 0) ? 1 : 0;
        return $this->writeToDatabase();
    }

    public function sage(): bool
    {
        if (!$this->dataLoaded(true))
        {
            return false;
        }

        $this->content_data['permasage'] = ($this->content_data['permasage'] == 0) ? 1 : 0;
        return $this->writeToDatabase();
    }

    public function cyclic(): bool
    {
        if (!$this->dataLoaded(true))
        {
            return false;
        }

        $this->content_data['cyclic'] = ($this->content_data['cyclic'] == 0) ? 1 : 0;
        return $this->writeToDatabase();
    }

    public function cycle(): void
    {
        $prepared = $this->database->prepare(
                'SELECT "post_number", "op" FROM "' . $this->posts_table .
                '" WHERE "parent_thread" = ? ORDER BY "post_number" DESC');
        $descending_post_list = $this->database->executePreparedFetchAll($prepared, [$this->content_id->threadID()],
                PDO::FETCH_ASSOC);

        if ($descending_post_list === false)
        {
            return;
        }

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
                $post->loadFromDatabase();
                $post->remove(true);
            }
        }
    }

    public function firstPostID(): int
    {
        $prepared = $this->database->prepare(
                'SELECT "post_number" FROM "' . $this->posts_table . '" WHERE "parent_thread" = ? AND "op" = 1');
        $first_post = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);
        return intval($first_post);
    }

    public function lastPostID(): int
    {
        $prepared = $this->database->prepare(
                'SELECT "post_number" FROM "' . $this->posts_table .
                '" WHERE "parent_thread" = ? ORDER BY "post_number" DESC LIMIT 1');
        $last_post = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);
        return intval($last_post);
    }

    public function lastBumpPostID(): int
    {
        $prepared = $this->database->prepare(
                'SELECT "post_number" FROM "' . $this->posts_table .
                '" WHERE "parent_thread" = ? AND "sage" = 0 ORDER BY "post_number" DESC LIMIT 1');
        $last_bump_post = $this->database->executePreparedFetch($prepared, [$this->content_id->threadID()],
                PDO::FETCH_COLUMN);
        return intval($last_bump_post);
    }

    public function getNthPostID(int $nth_post): int
    {
        $prepared = $this->database->prepare(
                'SELECT "post_number" FROM "' . $this->posts_table .
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

    public function isArchived()
    {
        return $this->content_data['archive_status'] == 2;
    }
}