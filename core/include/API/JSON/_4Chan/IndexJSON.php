<?php

namespace Nelliel\API\JSON\_4Chan;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\JSON;
use Nelliel\Domains\DomainBoard;

class IndexJSON extends JSON
{
    private $board;
    private $page;

    function __construct(DomainBoard $board, int $page)
    {
        $this->board = $board;
        $this->page = $page;
    }

    protected function generate(): void
    {
        $threads = $this->board->activeThreads(true);
        $offset = ($this->page - 1) * $this->board->setting('threads_per_page');
        $limit = $offset + $this->board->setting('threads_per_page');
        $thread_count = 0;

        foreach ($threads as $thread) {
            if ($thread_count >= $offset && $thread_count < $limit) {
                $this->raw_data['threads'][] = $thread->getJSON()->getRawData();
            }

            $thread_count ++;
        }

        $this->json = json_encode($this->raw_data);
        $this->needs_update = false;
    }
}