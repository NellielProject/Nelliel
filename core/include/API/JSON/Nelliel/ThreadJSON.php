<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON\Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\JSON;
use Nelliel\Content\Thread;

class ThreadJSON extends JSON
{
    private $thread;

    function __construct(Thread $thread)
    {
        $this->thread = $thread;
        $this->api_output = 'nelliel';
    }

    protected function generate(): void
    {
        $this->raw_data = array();
        $this->raw_data['api_output'] = $this->api_output;
        $this->raw_data['api_version'] = $this->api_version;
        $this->raw_data['thread_id'] = $this->thread->data('thread_id');
        $this->raw_data['bump_time'] = $this->thread->data('bump_time');
        $this->raw_data['bump_time_milli'] = $this->thread->data('bump_time_milli');
        $this->raw_data['last_update'] = $this->thread->data('last_update');
        $this->raw_data['last_update_milli'] = $this->thread->data('last_update_milli');
        $this->raw_data['post_count'] = $this->thread->data('post_count');
        $this->raw_data['bump_count'] = $this->thread->data('post_count');
        $this->raw_data['total_uploads'] = $this->thread->data('total_uploads');
        $this->raw_data['file_count'] = $this->thread->data('file_count');
        $this->raw_data['embed_count'] = $this->thread->data('embed_count');
        $this->raw_data['permasage'] = $this->thread->data('permasage');
        $this->raw_data['sticky'] = $this->thread->data('sticky');
        $this->raw_data['locked'] = $this->thread->data('locked');
        $this->raw_data['cyclic'] = $this->thread->data('cyclic');
        $this->raw_data['old'] = $this->thread->data('old');
        $this->raw_data['shadow'] = $this->thread->data('shadow');
        $this->raw_data['slug'] = $this->thread->data('slug');
        $posts = $this->thread->getPosts();

        foreach ($posts as $post) {
            $this->raw_data['posts'][] = $post->getJSON()->getRawData();
        }

        $this->json = json_encode($this->raw_data);
        $this->needs_update = false;
    }
}