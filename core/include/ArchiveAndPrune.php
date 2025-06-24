<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\DomainBoard;

class ArchiveAndPrune
{
    private DomainBoard $domain;

    function __construct(DomainBoard $domain)
    {
        $this->domain = $domain;
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
        $thread_list = $this->domain->getThreads();
        $page = 1;
        $page_size = $this->domain->setting('threads_per_page');
        $threads_on_page = 0;

        foreach ($thread_list as $thread) {
            if (!$thread->getData('preserve') && $early404 && $page > $early404_page &&
                $thread->getData('post_count') - 1 < $early404_replies &&
                $thread->getData('post_count') - 1 < $this->domain->setting('auto_archive_min_replies')) {
                $thread->delete(true);
                continue;
            } else if ($line <= $last_active) // Thread is within active range
            {
                $thread->changeData('old', false);
                $thread->writeToDatabase();
            } else if ($line <= $last_buffer) // Thread is within buffer range
            {
                $thread->changeData('old', true);
                $thread->writeToDatabase();
            } else if ($line <= $last_archive) // Thread is past buffer range
            {
                if ($archive || $thread->getData('preserve')) {
                    $thread->archive(false);
                    $thread->delete(true);
                    continue;
                }
            } else // Thread is beyond automatic archive range
            {
                if ($prune && $archive_prune) {
                    $thread->delete(true);
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
}
