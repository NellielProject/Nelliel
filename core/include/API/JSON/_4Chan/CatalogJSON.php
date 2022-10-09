<?php

namespace Nelliel\API\JSON\_4Chan;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\JSON;
use Nelliel\Domains\DomainBoard;

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
        // omitted_images (replies with images/uploads count)
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
                    if ($thread->data('sticky')) {
                        $raw_data['sticky'] = 1;
                    }

                    if ($thread->data('locked')) {
                        $raw_data['closed'] = 1;
                    }

                    $raw_data['replies'] = $thread->data('post_count') - 1;

                    // TODO: Update when bump stat is tracked
                    if ($thread->domain()->setting('limit_bump_count') &&
                        ($thread->data('post_count') >= $thread->domain()->setting('max_posts'))) {
                        $raw_data['bumplimit'] = 1;
                    }

                    if ($thread->domain()->setting('limit_thread_uploads') &&
                        ($thread->data('total_uploads') >= $thread->domain()->setting('max_thread_uploads'))) {
                        $raw_data['imagelimit'] = 1;
                    }

                    $raw_data['semantic_url'] = $thread->generateSlug($post);
                    $raw_data = $raw_data + $post->getJSON()->getRawData();
                    $omitted_posts = $thread->data('post_count') - $last_reply_count; // Subtract 1 to account for OP
                    $raw_data['omitted_posts'] = $omitted_posts > 0 ? $omitted_posts : 0;
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