<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;
use Nelliel\Content\ContentThread;
use Nelliel\Domains\Domain;
use PDO;

class ArchiveAndPrune
{
    private $database;
    private $file_handler;
    private $domain;

    function __construct(Domain $domain, $file_handler)
    {
        $this->database = $domain->database();
        $this->domain = $domain;
        $this->file_handler = $file_handler;
    }

    public function updateThreads()
    {
        if ($this->domain->setting('old_threads') === 'NOTHING')
        {
            return;
        }

        $this->updateAllArchiveStatus();

        if ($this->domain->setting('old_threads') === 'ARCHIVE')
        {
            foreach ($this->getThreadListForStatus(3) as $thread)
            {
                $this->moveFilesToArchive($thread);
            }

            foreach ($this->getThreadListForStatus(2) as $thread)
            {
                $this->moveFilesToArchive($thread);
            }

            foreach ($this->getThreadListForStatus(1) as $thread)
            {
                $this->moveFilesFromArchive($thread);
            }

            foreach ($this->getThreadListForStatus(0) as $thread)
            {
                $this->moveFilesFromArchive($thread);
            }

            if ($this->domain->setting('do_archive_pruning'))
            {
                $this->pruneArchiveThreads();
            }
        }
        else if ($this->domain->setting('old_threads') === 'PRUNE')
        {
            $this->pruneThreads();
        }
    }

    public function changeArchiveStatus($thread_id, int $status, string $table)
    {
        $prepared = $this->database->prepare('UPDATE "' . $table . '" SET "archive_status" = ? WHERE "thread_id" = ?');
        $this->database->executePrepared($prepared, [$status, $thread_id]);
    }

    public function getFullThreadList()
    {
        $query = 'SELECT "thread_id", "archive_status" FROM "' . $this->domain->reference('threads_table') .
                '" ORDER BY "sticky" DESC, "last_bump_time" DESC, "last_bump_time_milli" DESC';
        $thread_list = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);
        return $thread_list;
    }

    public function getThreadListForStatus(int $status)
    {
        $prepared = $this->database->prepare(
                'SELECT "thread_id" FROM "' . $this->domain->reference('threads_table') . '" WHERE "archive_status" = ?');
        $thread_list = $this->database->executePreparedFetchAll($prepared, [$status], PDO::FETCH_COLUMN);
        return $thread_list;
    }

    // Archie statuses:
    // 0: Thread normal status, displays in index
    // 1: Thread should be in buffer
    // 2: Thread should be in archive
    // 3: Thread should be pruned from archive
    public function updateAllArchiveStatus()
    {
        $line = 1;
        $last_active = $this->domain->setting('threads_per_page') * $this->domain->setting('page_limit');
        $last_buffer = $last_active + $this->domain->setting('thread_buffer');
        $last_archive = $last_buffer + $this->domain->setting('max_archive_threads');
        $archive_prune = $this->domain->setting('do_archive_pruning');
        $thread_table = $this->domain->reference('threads_table');
        $thread_list = $this->getFullThreadList();

        foreach ($thread_list as $thread)
        {
            if ($line <= $last_active)
            {
                if ($thread['archive_status'] != 0)
                {
                    $this->changeArchiveStatus($thread['thread_id'], 0, $thread_table);
                }
            }
            else if ($line <= $last_buffer)
            {
                if ($thread['archive_status'] != 1)
                {
                    $this->changeArchiveStatus($thread['thread_id'], 1, $thread_table);
                }
            }
            else if ($line <= $last_archive)
            {
                if ($thread['archive_status'] != 2)
                {
                    $this->changeArchiveStatus($thread['thread_id'], 2, $thread_table);
                }
            }
            else
            {
                if ($archive_prune)
                {
                    if ($thread['archive_status'] != 3)
                    {
                        $this->changeArchiveStatus($thread['thread_id'], 3, $thread_table);
                    }
                }
                else
                {
                    if ($thread['archive_status'] != 2)
                    {
                        $this->changeArchiveStatus($thread['thread_id'], 2, $thread_table);
                    }
                }
            }

            ++ $line;
        }
    }

    public function moveFilesToArchive($thread_id)
    {
        $this->file_handler->moveFile($this->domain->reference('src_path') . $thread_id,
                $this->domain->reference('archive_src_path') . $thread_id);
        $this->file_handler->moveFile($this->domain->reference('preview_path') . $thread_id,
                $this->domain->reference('archive_preview_path') . $thread_id);
        $this->file_handler->eraserGun($this->domain->reference('page_path') . $thread_id);
    }

    public function moveFilesFromArchive($thread_id)
    {
        $this->file_handler->moveFile($this->domain->reference('archive_src_path') . $thread_id,
                $this->domain->reference('src_path') . $thread_id);
        $this->file_handler->moveFile($this->domain->reference('archive_preview_path') . $thread_id,
                $this->domain->reference('preview_path') . $thread_id);
    }

    public function pruneThreads()
    {
        foreach ($this->getThreadListForStatus(2) as $thread_id)
        {
            $thread = new ContentThread(new ContentID('cid_' . $thread_id . '_0_0'), $this->domain);
            $thread->remove(true);
        }

        $this->pruneArchiveThreads();
    }

    public function pruneArchiveThreads()
    {
        foreach ($this->getThreadListForStatus(3) as $thread_id)
        {
            $thread = new ContentThread(new ContentID('cid_' . $thread_id . '_0_0'), $this->domain, true);
            $thread->remove(true);
        }
    }
}
