<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\DomainBoard;

class IndexJSON extends JSON
{
    private DomainBoard $board;
    private int $page;

    function __construct(DomainBoard $board, int $page)
    {
        $this->board = $board;
        $this->page = $page;
    }

    protected function generate(): void
    {
        $raw_data = array();
        $threads = $this->board->getThreads(true, false);
        $offset = ($this->page - 1) * $this->board->setting('threads_per_page');
        $limit = $offset + $this->board->setting('threads_per_page');
        $thread_count = 0;
        $index_replies = $this->board->setting('index_thread_replies');
        $index_sticky_replies = $this->board->setting('index_sticky_replies');

        foreach ($threads as $thread) {
            if ($thread_count >= $offset && $thread_count < $limit) {
                $thread_data = array();
                $thread_data = $thread->getJSON()->getRawData();
                $thread_data['posts'] = array($thread->firstPost()->getJSON()->getRawData());
                $last_reply_count = $thread->getData('sticky') ? $index_sticky_replies : $index_replies;

                foreach ($thread->lastReplies($last_reply_count) as $post) {
                    $thread_data['posts'][] = $post->getJSON()->getRawData();
                }

                $omitted_posts = $thread->getData('post_count') - $last_reply_count; // Subtract 1 to account for OP
                $thread_data['omitted_posts'] = $omitted_posts > 0 ? $omitted_posts : 0;
                $raw_data['threads'][] = $thread_data;
            }

            $thread_count ++;
        }

        $raw_data = nel_plugins()->processHook('nel-in-after-index-json', [$this->board, $this->page], $raw_data);
        $this->raw_data = $raw_data;
        $this->needs_update = false;
    }
}