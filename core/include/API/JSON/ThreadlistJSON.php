<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\DomainBoard;

class ThreadlistJSON extends JSON
{
    private $board;

    function __construct(DomainBoard $board)
    {
        $this->board = $board;
    }

    protected function generate(): void
    {
        $raw_data = array();
        $threads = $this->board->activeThreads(true);
        $page = 1;
        $page_threads = array_chunk($threads, $this->board->setting('threads_per_page'));

        foreach ($page_threads as $thread_set) {
            $page_data = array();
            $page_data['page'] = $page;

            foreach ($thread_set as $thread) {
                $thread_data = array();
                $thread_data['thread_id'] = $thread->contentID()->threadID();
                $thread_data['last_update'] = $thread->data('last_update');
                $thread_data['replies'] = $thread->data('post_count') - 1;
                $page_data['threads'][] = $thread_data;
            }

            $raw_data[] = $page_data;
            $page ++;
        }

        $raw_data = nel_plugins()->processHook('nel-in-after-threadlist-json', [$this->board], $raw_data);
        $this->raw_data = $raw_data;
        $this->needs_update = false;
    }
}