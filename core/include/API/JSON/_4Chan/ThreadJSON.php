<?php

namespace Nelliel\API\JSON\_4Chan;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\JSON;
use Nelliel\Content\Thread;

class ThreadJSON extends JSON
{
    private $thread;

    function __construct(Thread $thread)
    {
        $this->thread = $thread;
    }

    protected function generate(): void
    {
        // TODO: implement (and related functions if needed)
        // images (replies with images/uploads count)
        // unique_ips (how many unique posters in a thread
        // archived
        // archived_on (timestamp of when archived)
        foreach ($this->thread->getPosts() as $post) {
            $raw_data = array();

            if ($post->data('op')) {
                if ($this->thread->data('sticky')) {
                    $raw_data['sticky'] = 1;
                }

                if ($this->thread->data('locked')) {
                    $raw_data['closed'] = 1;
                }

                $raw_data['replies'] = $this->thread->data('post_count') - 1;

                // TODO: Update when bump stat is tracked
                if ($this->thread->domain()->setting('limit_bump_count') &&
                    ($this->thread->data('post_count') >= $this->thread->domain()->setting('max_posts'))) {
                    $raw_data['bumplimit'] = 1;
                }

                if ($this->thread->domain()->setting('limit_thread_uploads') &&
                    ($this->thread->data('total_uploads') >= $this->thread->domain()->setting('max_thread_uploads'))) {
                    $raw_data['imagelimit'] = 1;
                }

                $raw_data['semantic_url'] = $this->thread->generateSlug($post);
            }

            $raw_data = $raw_data +  $post->getJSON()->getRawData();
            $this->raw_data['posts'][] = $raw_data;
        }

        $this->json = json_encode($this->raw_data);
        $this->needs_update = false;
    }
}