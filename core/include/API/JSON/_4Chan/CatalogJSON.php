<?php

namespace Nelliel\API\JSON\_4Chan;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\JSON;
use Nelliel\Domains\DomainBoard;
use PDO;

class CatalogJSON extends JSON
{
    private $board;

    function __construct(DomainBoard $board)
    {
        $this->board = $board;
    }

    protected function generate(): void
    {
        // TODO: implement (and related functions if needed)
        // images (replies with images/uploads count)
        // unique_ips (how many unique posters in a thread
        // archived
        // archived_on (timestamp of when archived)
        $threads = $this->board->activeThreads(true);
        $threads_per_page = $this->board->setting('threads_per_page');
        $threads_on_page = 0;
        $page = 1;
        $page_data = ['page' => $page];
        $index_replies = $this->board->setting('index_thread_replies');
        $index_sticky_replies = $this->board->setting('index_sticky_replies');

        foreach ($threads as $thread) {
            if ($threads_on_page === $threads_per_page) {
                $this->raw_data[] = $page_data;
                $page_data = ['page' => $page];
                $page ++;
                $threads_on_page = 0;
            }

            $last_reply_count = $thread->data('sticky') ? $index_sticky_replies : $index_replies;
            $abbreviate_start = $thread->data('post_count') - $last_reply_count;
            $post_counter = 1;
            $raw_data = array();

            foreach ($thread->getPosts() as $post) {
                if ($post->data('op')) {
                    $op_json = new OPJSON($thread);
                    $raw_data = $op_json->getRawData();
                    $raw_data = $raw_data + $post->getJSON()->getRawData();

                    // 4Chan's output actually violates their own spec since all replies are omitted in the catalog
                    $raw_data['omitted_posts'] = $thread->data('post_count') - 1;
                    $raw_data['omitted_images'] = 0; // TODO: Implement
                } else {
                    if ($post_counter > $abbreviate_start) {
                        $raw_data['last_replies'][] = $post->getJSON()->getRawData();
                    }
                }

                $post_counter ++;
            }

            $raw_data['last_modified'] = $thread->data('last_update'); // TODO: Make sure this is being changed for all changes
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