<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\ContentID;
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

        $line = 1;
        $last_active = $this->domain->setting('active_threads');
        $last_buffer = $last_active + $this->domain->setting('thread_buffer');
        $last_archive = $last_buffer + $this->domain->setting('max_archive_threads');
        $archive_prune = $this->domain->setting('do_archive_pruning');
        $archive = $this->domain->setting('old_threads') === 'ARCHIVE';
        $prune = $this->domain->setting('old_threads') === 'PRUNE';
        $thread_list = $this->getFullThreadList();

        foreach ($thread_list as $thread)
        {
            $content_id = new ContentID();
            $content_id->changeThreadID($thread['thread_id']);

            if ($line <= $last_active) // Thread is within active range
            {
                if ($thread['old'] == 1)
                {
                    $thread = $content_id->getInstanceFromID($this->domain);
                    $thread->changeData('old', false);
                    $thread->writeToDatabase();
                }
            }
            else if ($line <= $last_buffer) // Thread is within buffer range
            {
                if ($thread['old'] == 0)
                {
                    $thread = $content_id->getInstanceFromID($this->domain);
                    $thread->changeData('old', true);
                    $thread->writeToDatabase();
                }
            }
            else if ($line <= $last_archive) // Thread is past buffer range
            {
                if ($archive)
                {
                    $thread = $content_id->getInstanceFromID($this->domain);
                    $thread->archive();
                }
            }
            else // Thread is beyond automatic archive range
            {
                if ($prune && $archive_prune)
                {
                    $thread = $content_id->getInstanceFromID($this->domain);
                    $thread->remove();
                }
            }

            $line ++;
        }
    }

    private function getFullThreadList()
    {
        $query = 'SELECT "thread_id", "old" FROM "' . $this->domain->reference('threads_table') .
                '" ORDER BY "sticky" DESC, "last_bump_time" DESC, "last_bump_time_milli" DESC';
        $thread_list = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);
        return $thread_list;
    }
}
