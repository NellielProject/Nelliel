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
        $index_replies = $this->board->setting('index_thread_replies');
        $index_sticky_replies = $this->board->setting('index_sticky_replies');

        foreach ($threads as $thread) {
            if ($thread_count >= $offset && $thread_count < $limit) {
                $raw_data = array();
                $raw_data = $thread->getJSON()->getRawData();
                $last_reply_count = $thread->data('sticky') ? $index_sticky_replies : $index_replies;
                $omitted_posts = $thread->data('post_count') - $last_reply_count; // Subtract 1 to account for OP
                $raw_data['posts'][0]['omitted_posts'] = $omitted_posts > 0 ? $omitted_posts : 0;
                $raw_data['posts'][0]['omitted_images'] = 0; // TODO: Implement
                $this->raw_data['threads'][] = $raw_data;
            }

            $thread_count ++;
        }

        $this->json = json_encode($this->raw_data);
        $this->needs_update = false;
    }
}