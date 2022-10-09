<?php

namespace Nelliel\API\JSON\_4Chan;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\JSON;
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
        $threads = $this->board->activeThreads(true);
        $threads_per_page = $this->board->setting('threads_per_page');
        $threads_on_page = 0;
        $page = 1;
        $page_data = ['page' => $page];

        foreach ($threads as $thread) {
            if ($threads_on_page === $threads_per_page) {
                $this->raw_data[] = $page_data;
                $page_data = ['page' => $page];
                $page ++;
                $threads_on_page = 0;
            }

            $raw_data = array();
            $raw_data['no'] = $thread->firstPost()->data('post_number');
            $raw_data['last_modified'] = $thread->data('last_update'); // TODO: Make sure this is being changed for all changes
            $raw_data['replies'] = $thread->data('post_count') - 1;
            $page_data['threads'][] = $raw_data;
            $threads_on_page ++;
        }

        if (!empty($page_data)) {
            $this->raw_data[] = $page_data;
        }

        $this->json = json_encode($this->raw_data);
        $this->needs_update = false;
    }
}