<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ArchiveAndPrune
{
    private $dbh;
    private $file_handler;
    private $start_buffer;
    private $end_buffer;
    private $board_id;
    private $references;
    private $board_settings;

    function __construct($database, $board_id)
    {
        $this->dbh = $database;
        $this->start_buffer = nel_parameters_and_data()->boardSettings($board_id, 'threads_per_page') *
                nel_parameters_and_data()->boardSettings($board_id, 'page_limit');
        $this->end_buffer = nel_parameters_and_data()->boardSettings($board_id, 'threads_per_page') *
                nel_parameters_and_data()->boardSettings($board_id, 'page_buffer');
        $this->file_handler = new \Nelliel\FileHandler();
        $this->board_id = $board_id;
        $this->references = nel_parameters_and_data()->boardReferences($board_id);
        $this->board_settings = nel_parameters_and_data()->boardSettings($board_id);

        if ($this->end_buffer == 0)
        {
            $this->end_buffer = $this->start_buffer;
        }
    }

    public function changeArchiveStatus($thread_id, $status, $table)
    {
        $prepared = $this->dbh->prepare('UPDATE "' . $table . '" SET "archive_status" = ? WHERE "thread_id" = ?');
        $this->dbh->executePrepared($prepared, array($status, $thread_id));
    }

    public function getFullThreadList()
    {
        $query = 'SELECT "thread_id", "archive_status" FROM "' . $this->references['thread_table'] .
                '" ORDER BY "sticky" DESC, "last_bump_time" DESC';
        $thread_list = $this->dbh->executeFetchAll($query, PDO::FETCH_ASSOC);
        return $thread_list;
    }

    public function getThreadListForStatus($status)
    {
        $prepared = $this->dbh->prepare(
                'SELECT "thread_id" FROM "' . $this->references['thread_table'] . '" WHERE "archive_status" = ?');
        $thread_list = $this->dbh->executePreparedFetchAll($prepared, array($status), PDO::FETCH_COLUMN);
        return $thread_list;
    }

    public function getFullArchiveThreadList()
    {
        $query = 'SELECT "thread_id", "archive_status" FROM "' . $this->references['archive_thread_table'] .
                '" ORDER BY "sticky" DESC, "last_bump_time" DESC';
        $thread_list = $this->dbh->executeFetchAll($query, PDO::FETCH_ASSOC);
        return $thread_list;
    }

    public function updateAllArchiveStatus()
    {
        if ($this->board_settings['old_threads'] === 'NOTHING')
        {
            return;
        }

        $line = 1;

        foreach ($this->getFullThreadList() as $thread)
        {
            if ($line <= $this->start_buffer && $thread['archive_status'] != 0)
            {
                $this->changeArchiveStatus($thread['thread_id'], 0, $this->references['thread_table']);
            }
            else if ($line > $this->start_buffer && $line <= $this->end_buffer && $thread['archive_status'] != 1)
            {
                $this->changeArchiveStatus($thread['thread_id'], 1, $this->references['thread_table']);
            }
            else if ($line > $this->end_buffer && $thread['archive_status'] != 2)
            {
                $this->changeArchiveStatus($thread['thread_id'], 2, $this->references['thread_table']);
            }

            ++ $line;
        }

        foreach ($this->getFullArchiveThreadList() as $thread)
        {
            if ($line <= $this->start_buffer && $thread['archive_status'] != 0)
            {
                $this->changeArchiveStatus($thread['thread_id'], 0, $this->references['archive_thread_table']);
            }
            else if ($line > $this->start_buffer && $line <= $this->end_buffer && $thread['archive_status'] != 1)
            {
                $this->changeArchiveStatus($thread['thread_id'], 1, $references['archive_thread_table']);
            }
            else if ($line > $this->end_buffer && $thread['archive_status'] != 2)
            {
                $this->changeArchiveStatus($thread['thread_id'], 2, $references['archive_thread_table']);
            }

            ++ $line;
        }
    }

    public function moveToArchive($thread_id)
    {
        // Threads must be moved first, then posts, then files otherwise the db foreign keys will bitch
        $prepared = $this->dbh->prepare(
                'INSERT INTO "' . $this->references['archive_thread_table'] . '" SELECT * FROM "' .
                $this->references['thread_table'] . '" WHERE "thread_id" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare(
                'INSERT INTO "' . $this->references['archive_post_table'] . '" SELECT * FROM "' .
                $this->references['post_table'] . '" WHERE "parent_thread" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare(
                'INSERT INTO "' . $this->references['archive_file_table'] . '" SELECT * FROM "' .
                $this->references['file_table'] . '" WHERE "parent_thread" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $this->file_handler->moveFile($this->references['src_path'] . $thread_id,
                $this->references['archive_src_path'] . $thread_id);
        $this->file_handler->moveFile($this->references['thumb_path'] . $thread_id,
                $this->references['archive_thumb_path'] . $thread_id);
        $this->file_handler->moveFile($references['page_path'] . $thread_id,
                $this->references['archive_page_path'] . $thread_id);
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare('DELETE FROM "' . $this->references['thread_table'] . '" WHERE "thread_id"= ?');
    }

    public function moveFromArchive($thread_id)
    {
        // Threads must be moved first, then posts, then files otherwise the db foreign keys will bitch
        $prepared = $this->dbh->prepare(
                'INSERT INTO "' . $this->references['thread_table'] . '" SELECT * FROM "' .
                $this->references['archive_thread_table'] . '" WHERE "thread_id" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare(
                'INSERT INTO "' . $this->references['post_table'] . '" SELECT * FROM "' .
                $this->references['archive_post_table'] . '" WHERE "parent_thread" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare(
                'INSERT INTO "' . $this->references['file_table'] . '" SELECT * FROM "' .
                $this->references['archive_file_table'] . '" WHERE "parent_thread" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $this->file_handler->moveFile($this->references['archive_src_path'] . $thread_id,
                $this->references['src_path'] . $thread_id);
        $this->file_handler->moveFile($this->references['archive_thumb_path'] . $thread_id,
                $this->references['thumb_path'] . $thread_id);
        $this->file_handler->moveFile($this->references['archive_page_path'] . $thread_id,
                $this->references['page_path'] . $thread_id);
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare(
                'DELETE FROM "' . $this->references['archive_thread_table'] . '" WHERE "thread_id"= ?');
    }

    public function moveThreadsToArchive($move_list = array())
    {
        if (empty($move_list))
        {
            $query = 'SELECT "thread_id" FROM "' . $this->references['thread_table'] . '" WHERE "archive_status" = 2';
            $move_list = $this->dbh->executeFetchAll($query, PDO::FETCH_COLUMN);
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
            $query = 'SELECT "thread_id" FROM "' . $this->references['archive_thread_table'] .
                    '" WHERE "archive_status" < 2';
            $move_list = $this->dbh->executeFetchAll($query, PDO::FETCH_COLUMN);
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
            $thread = new \Nelliel\ContentThread($this->dbh, new \Nelliel\ContentID('nci_' . $thread_id . '_0_0'),
                    $this->board_id);
            $thread->remove();
        }
    }
}
