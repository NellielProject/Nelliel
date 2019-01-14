<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ArchiveAndPrune
{
    private $database;
    private $file_handler;
    private $start_buffer;
    private $end_buffer;
    private $domain;

    function __construct($database, $domain, $file_handler)
    {
        $this->database = $database;
        $this->domain = $domain;
        $this->start_buffer = $domain->setting('threads_per_page') * $domain->setting('page_limit');
        $this->end_buffer = $domain->setting('threads_per_page') * $domain->setting('page_buffer');
        $this->file_handler = $file_handler;

        if ($this->end_buffer == 0)
        {
            $this->end_buffer = $this->start_buffer;
        }
    }

    public function updateThreads()
    {
        $this->updateAllArchiveStatus();

        if ($this->domain->setting('old_threads') === 'ARCHIVE')
        {
            $this->moveThreadsToArchive();
            $this->moveThreadsFromArchive();
        }
        else if ($this->domain->setting('old_threads') === 'PRUNE')
        {
            $this->pruneThreads();
        }
    }

    public function changeArchiveStatus($thread_id, $status, $table)
    {
        $prepared = $this->database->prepare('UPDATE "' . $table . '" SET "archive_status" = ? WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$status, $thread_id]);
    }

    public function getFullThreadList()
    {
        $query = 'SELECT "thread_id", "archive_status" FROM "' . $this->domain->reference('thread_table') .
                '" ORDER BY "sticky" DESC, "last_bump_time" DESC, "last_bump_time_milli" DESC';
        $thread_list = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);
        return $thread_list;
    }

    public function getThreadListForStatus($status)
    {
        $prepared = $this->database->prepare(
                'SELECT "thread_id" FROM "' . $this->domain->reference('thread_table') . '" WHERE "archive_status" = ?');
        $thread_list = $this->database->executePreparedFetchAll($prepared, [$status], PDO::FETCH_COLUMN);
        return $thread_list;
    }

    public function getFullArchiveThreadList()
    {
        $query = 'SELECT "thread_id", "archive_status" FROM "' . $this->domain->reference('archive_thread_table') .
                '" ORDER BY "sticky" DESC, "last_bump_time" DESC, "last_bump_time_milli" DESC';
        $thread_list = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);
        return $thread_list;
    }

    public function updateAllArchiveStatus()
    {
        if ($this->domain->setting('old_threads') === 'NOTHING')
        {
            return;
        }

        $line = 1;

        foreach ($this->getFullThreadList() as $thread)
        {
            if ($line <= $this->start_buffer && $thread['archive_status'] != 0)
            {
                $this->changeArchiveStatus($thread['thread_id'], 0, $this->domain->reference('thread_table'));
            }
            else if ($line > $this->start_buffer && $line <= $this->end_buffer && $thread['archive_status'] != 1)
            {
                $this->changeArchiveStatus($thread['thread_id'], 1, $this->domain->reference('thread_table'));
            }
            else if ($line > $this->end_buffer && $thread['archive_status'] != 2)
            {
                $this->changeArchiveStatus($thread['thread_id'], 2, $this->domain->reference('thread_table'));
            }

            ++ $line;
        }

        foreach ($this->getFullArchiveThreadList() as $thread)
        {
            if ($line <= $this->start_buffer && $thread['archive_status'] != 0)
            {
                $this->changeArchiveStatus($thread['thread_id'], 0, $this->domain->reference('archive_thread_table'));
            }
            else if ($line > $this->start_buffer && $line <= $this->end_buffer && $thread['archive_status'] != 1)
            {
                $this->changeArchiveStatus($thread['thread_id'], 1, $this->domain->reference('archive_thread_table'));
            }
            else if ($line > $this->end_buffer && $thread['archive_status'] != 2)
            {
                $this->changeArchiveStatus($thread['thread_id'], 2, $this->domain->reference('archive_thread_table'));
            }

            ++ $line;
        }
    }

    public function moveToArchive($thread_id)
    {
        // Threads must be moved first, then posts, then files otherwise the db foreign keys will bitch
        $prepared = $this->database->prepare(
                'INSERT INTO "' . $this->domain->reference('archive_thread_table') . '" SELECT * FROM "' .
                $this->domain->reference('thread_table') . '" WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$thread_id]);
        $prepared = $this->database->prepare(
                'INSERT INTO "' . $this->domain->reference('archive_post_table') . '" SELECT * FROM "' .
                $this->domain->reference('post_table') . '" WHERE "parent_thread" = ?');
        $this->database->executePrepared($prepared, [$thread_id]);
        $prepared = $this->database->prepare(
                'INSERT INTO "' . $this->domain->reference('archive_content_table') . '" SELECT * FROM "' .
                $this->domain->reference('content_table') . '" WHERE "parent_thread" = ?');
        $this->database->executePrepared($prepared, [$thread_id]);
        $this->file_handler->moveFile($this->domain->reference('src_path') . $thread_id,
                $this->domain->reference('archive_src_path') . $thread_id);
        $this->file_handler->moveFile($this->domain->reference('thumb_path') . $thread_id,
                $this->domain->reference('archive_thumb_path') . $thread_id);
        $this->file_handler->moveFile($this->domain->reference('page_path') . $thread_id,
                $this->domain->reference('archive_page_path') . $thread_id);
        $prepared = $this->database->prepare(
                'DELETE FROM "' . $this->domain->reference('thread_table') . '" WHERE "thread_id"= ?');
        $this->database->executePrepared($prepared, [$thread_id]);
    }

    public function moveFromArchive($thread_id)
    {
        // Threads must be moved first, then posts, then files otherwise the db foreign keys will bitch
        $prepared = $this->database->prepare(
                'INSERT INTO "' . $this->domain->reference('thread_table') . '" SELECT * FROM "' .
                $this->domain->reference('archive_thread_table') . '" WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$thread_id]);
        $prepared = $this->database->prepare(
                'INSERT INTO "' . $this->domain->reference('post_table') . '" SELECT * FROM "' .
                $this->domain->reference('archive_post_table') . '" WHERE "parent_thread" = ?');
        $this->database->executePrepared($prepared, [$thread_id]);
        $prepared = $this->database->prepare(
                'INSERT INTO "' . $this->domain->reference('content_table') . '" SELECT * FROM "' .
                $this->domain->reference('archive_content_table') . '" WHERE "parent_thread" = ?');
        $this->database->executePrepared($prepared, [$thread_id]);
        $this->file_handler->moveFile($this->domain->reference('archive_src_path') . $thread_id,
                $this->domain->reference('src_path') . $thread_id);
        $this->file_handler->moveFile($this->domain->reference('archive_thumb_path') . $thread_id,
                $this->domain->reference('thumb_path') . $thread_id);
        $this->file_handler->moveFile($this->domain->reference('archive_page_path') . $thread_id,
                $this->domain->reference('page_path') . $thread_id);
        $prepared = $this->database->prepare(
                'DELETE FROM "' . $this->domain->reference('archive_thread_table') . '" WHERE "thread_id"= ?');
        $this->database->executePrepared($prepared, [$thread_id]);
    }

    public function moveThreadsToArchive($move_list = array())
    {
        if (empty($move_list))
        {
            $query = 'SELECT "thread_id" FROM "' . $this->domain->reference('thread_table') .
                    '" WHERE "archive_status" = 2';
            $move_list = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);
        }

        foreach ($move_list as $thread_id)
        {
            $this->moveToArchive($thread_id);
        }
    }

    public function moveThreadsFromArchive($move_list = array())
    {
        if (empty($move_list))
        {
            $query = 'SELECT "thread_id" FROM "' . $this->domain->reference('archive_thread_table') .
                    '" WHERE "archive_status" < 2';
            $move_list = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);
        }

        foreach ($move_list as $thread_id)
        {
            $this->moveFromArchive($thread_id);
        }
    }

    public function pruneThreads()
    {
        foreach ($this->getThreadListForStatus(2) as $thread_id)
        {
            $thread = new \Nelliel\Content\ContentThread($this->database, new ContentID('cid_' . $thread_id . '_0_0'),
                    $this->domain);
            $thread->remove(true);
        }
    }
}
