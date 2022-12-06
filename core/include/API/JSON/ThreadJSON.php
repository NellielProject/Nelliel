<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

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
        $raw_data = array();
        $raw_data['thread_id'] = $this->thread->data('thread_id');
        $raw_data['bump_time'] = $this->thread->data('bump_time');
        $raw_data['bump_time_milli'] = $this->thread->data('bump_time_milli');
        $raw_data['last_update'] = $this->thread->data('last_update');
        $raw_data['last_update_milli'] = $this->thread->data('last_update_milli');
        $raw_data['post_count'] = $this->thread->data('post_count');
        $raw_data['bump_count'] = $this->thread->data('post_count');
        $raw_data['total_uploads'] = $this->thread->data('total_uploads');
        $raw_data['file_count'] = $this->thread->data('file_count');
        $raw_data['embed_count'] = $this->thread->data('embed_count');
        $raw_data['permasage'] = $this->thread->data('permasage');
        $raw_data['sticky'] = $this->thread->data('sticky');
        $raw_data['locked'] = $this->thread->data('locked');
        $raw_data['cyclic'] = $this->thread->data('cyclic');
        $raw_data['old'] = $this->thread->data('old');
        $raw_data['shadow'] = $this->thread->data('shadow');
        $raw_data['slug'] = $this->thread->data('slug');
        $posts = $this->thread->getPosts();

        foreach ($posts as $post) {
            $raw_data['posts'][] = $post->getJSON()->getRawData();
        }

        $raw_data = nel_plugins()->processHook('nel-in-during-thread-json', [$this->thread], $raw_data);
        $this->raw_data = $raw_data;
        $this->json = json_encode($raw_data);
        $this->needs_update = false;
    }
}