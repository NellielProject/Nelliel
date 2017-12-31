<?php

namespace Nelliel;

use \PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class ArchiveAndPrune
{
    private $start_buffer;
    private $end_buffer;

    function __construct()
    {
        $this->dbh = nel_database();
        $this->start_buffer = nel_board_settings('threads_per_page') * nel_board_settings('page_limit');
        $this->end_buffer = nel_board_settings('threads_per_page') * nel_board_settings('page_buffer');

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

    public function getThreadList()
    {
        $query = 'SELECT "thread_id", "archive_status" FROM "' . THREAD_TABLE .
        '" ORDER BY "sticky" DESC, "last_bump_time" DESC';
        $thread_list = $this->dbh->executeFetchAll($query, PDO::FETCH_ASSOC);
        return $thread_list;
    }

    public function getArchiveThreadList()
    {
        $query = 'SELECT "thread_id", "archive_status" FROM "' . ARCHIVE_THREAD_TABLE .
        '" ORDER BY "sticky" DESC, "last_bump_time" DESC';
        $thread_list = $this->dbh->executeFetchAll($query, PDO::FETCH_ASSOC);
        return $thread_list;
    }

    public function updateAllArchiveStatus()
    {
        if (BS_OLD_THREADS === 'NOTHING')
        {
            return;
        }

        $line = 1;

        foreach ($this->getThreadList() as $thread)
        {
            if ($line <= $this->start_buffer && $thread['archive_status'] != 0)
            {
                $this->changeArchiveStatus($thread['thread_id'], 0, THREAD_TABLE);
            }
            else if ($line > $this->start_buffer && $line <= $this->end_buffer && $thread['archive_status'] != 1)
            {
                $this->changeArchiveStatus($thread['thread_id'], 1, THREAD_TABLE);
            }
            else if ($line > $this->end_buffer && $thread['archive_status'] != 2)
            {
                $this->changeArchiveStatus($thread['thread_id'], 2, THREAD_TABLE);
            }

            ++ $line;
        }

        foreach ($this->getArchiveThreadList() as $thread)
        {
            if ($line <= $this->start_buffer && $thread['archive_status'] != 0)
            {
                $this->changeArchiveStatus($thread['thread_id'], 0, ARCHIVE_THREAD_TABLE);
            }
            else if ($line > $this->start_buffer && $line <= $this->end_buffer && $thread['archive_status'] != 1)
            {
                $this->changeArchiveStatus($thread['thread_id'], 1, ARCHIVE_THREAD_TABLE);
            }
            else if ($line > $this->end_buffer && $thread['archive_status'] != 2)
            {
                $this->changeArchiveStatus($thread['thread_id'], 2, ARCHIVE_THREAD_TABLE);
            }

            ++ $line;
        }
    }

    // BS_OLD_THREADS === 'ARCHIVE'  BS_OLD_THREADS === 'PRUNE'
    public function moveToArchive($thread_id)
    {
        $prepared = $this->dbh->prepare('INSERT INTO "' . ARCHIVE_POST_TABLE . '" SELECT * FROM "' . POST_TABLE .
        '" WHERE "parent_thread" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare('INSERT INTO "' . ARCHIVE_THREAD_TABLE . '" SELECT * FROM "' . THREAD_TABLE .
        '" WHERE "thread_id" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare('INSERT INTO "' . ARCHIVE_FILE_TABLE . '" SELECT * FROM "' . FILE_TABLE .
        '" WHERE "parent_thread" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        nel_move_file(SRC_PATH . $thread_id, ARC_SRC_PATH . $thread_id);
        nel_move_file(THUMB_PATH . $thread_id, ARC_THUMB_PATH . $thread_id);
        nel_move_file(PAGE_PATH . $thread_id, ARC_PAGE_PATH . $thread_id);
        $prepared = $this->dbh->prepare('DELETE FROM "' . POST_TABLE . '" WHERE "parent_thread" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare('DELETE FROM "' . THREAD_TABLE . '" WHERE "thread_id"= ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare('DELETE FROM "' . FILE_TABLE . '" WHERE "parent_thread"= ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
    }

    public function moveFromArchive($thread_id)
    {
        $prepared = $this->dbh->prepare('INSERT INTO "' . POST_TABLE . '" SELECT * FROM "' . ARCHIVE_POST_TABLE .
        '" WHERE "parent_thread" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare('INSERT INTO "' . THREAD_TABLE . '" SELECT * FROM "' . ARCHIVE_THREAD_TABLE .
        '" WHERE "thread_id" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare('INSERT INTO "' . FILE_TABLE . '" SELECT * FROM "' . ARCHIVE_FILE_TABLE .
        '" WHERE "parent_thread" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        nel_move_file(ARC_SRC_PATH . $thread_id, SRC_PATH . $thread_id);
        nel_move_file(ARC_THUMB_PATH . $thread_id, THUMB_PATH . $thread_id);
        nel_move_file(ARC_PAGE_PATH . $thread_id, PAGE_PATH . $thread_id);
        $prepared = $this->dbh->prepare('DELETE FROM "' . ARCHIVE_POST_TABLE . '" WHERE "parent_thread" = ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare('DELETE FROM "' . ARCHIVE_THREAD_TABLE . '" WHERE "thread_id"= ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
        $prepared = $this->dbh->prepare('DELETE FROM "' . ARCHIVE_FILE_TABLE . '" WHERE "parent_thread"= ?');
        $this->dbh->executePrepared($prepared, array($thread_id));
    }

    public function moveThreadsToArchive($move_list = array())
    {
        if (empty($move_list))
        {
            $query = 'SELECT "thread_id" FROM "' . THREAD_TABLE . '" WHERE "archive_status" = 2';
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
            $query = 'SELECT "thread_id" FROM "' . ARCHIVE_THREAD_TABLE . '" WHERE "archive_status" < 2';
            $move_list = $this->dbh->executeFetchAll($query, PDO::FETCH_COLUMN);
        }

        foreach ($move_list as $thread_id)
        {
            $this->moveFromArchive($thread_id);
        }
    }
}
