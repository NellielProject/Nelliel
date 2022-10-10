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
        // unique_ips (how many unique posters in a thread
        // archived
        // archived_on (timestamp of when archived)
        foreach ($this->thread->getPosts() as $post) {
            $raw_data = array();

            if ($post->data('op')) {
                $op_json = new OPJSON($this->thread);
                $raw_data = $op_json->getRawData();
            }

            $raw_data = $raw_data + $post->getJSON()->getRawData();
            $this->raw_data['posts'][] = $raw_data;
        }

        $this->json = json_encode($this->raw_data);
        $this->needs_update = false;
    }
}