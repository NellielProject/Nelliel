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
        $early404 = $this->domain->setting('enable_early404');
        $early404_replies = $this->domain->setting('early404_replies_threshold');
        $early404_page = $this->domain->setting('early404_page_threshold');

        if ($this->domain->setting('old_threads') === 'NOTHING' && !$early404) {
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
        $page = 1;
        $page_size = $this->domain->setting('threads_per_page');
        $threads_on_page = 0;

        foreach ($thread_list as $thread) {
            $content_id = new ContentID();
            $content_id->changeThreadID($thread['thread_id']);

            if ($thread['preserve'] != 1 && $early404 && $page > $early404_page &&
                $thread['post_count'] - 1 < $early404_replies) {
                $thread = $content_id->getInstanceFromID($this->domain);
                $thread->remove(true);
                continue;
            }

            if ($line <= $last_active) // Thread is within active range
            {
                if ($thread['old'] == 1) {
                    $thread = $content_id->getInstanceFromID($this->domain);
                    $thread->changeData('old', false);
                    $thread->writeToDatabase();
                }
            } else if ($line <= $last_buffer) // Thread is within buffer range
            {
                if ($thread['old'] == 0) {
                    $thread = $content_id->getInstanceFromID($this->domain);
                    $thread->changeData('old', true);
                    $thread->writeToDatabase();
                }
            } else if ($line <= $last_archive) // Thread is past buffer range
            {
                if ($archive) {
                    $thread = $content_id->getInstanceFromID($this->domain);
                    $thread->archive(false);
                    continue;
                }
            } else // Thread is beyond automatic archive range
            {
                if ($prune && $archive_prune) {
                    $thread = $content_id->getInstanceFromID($this->domain);
                    $thread->remove(true);
                    continue;
                }
            }

            $threads_on_page ++;

            if ($threads_on_page === $page_size) {
                $page ++;
                $threads_on_page = 0;
            }
            $line ++;
        }
    }

    private function getFullThreadList()
    {
        $query = 'SELECT "thread_id", "post_count", "old", "preserve" FROM "' . $this->domain->reference(
            'threads_table') . '" ORDER BY "sticky" DESC, "bump_time" DESC, "bump_time_milli" DESC';
        $thread_list = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);
        return $thread_list;
    }
}
