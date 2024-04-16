<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Thread;

class ThreadJSON extends JSON
{
    private Thread $thread;

    function __construct(Thread $thread)
    {
        $this->thread = $thread;
    }

    protected function generate(): void
    {
        $raw_data = array();
        $raw_data['thread_id'] = $this->thread->getData('thread_id');
        $raw_data['bump_time'] = $this->thread->getData('bump_time');
        $raw_data['bump_time_milli'] = $this->thread->getData('bump_time_milli');
        $raw_data['last_update'] = $this->thread->getData('last_update');
        $raw_data['last_update_milli'] = $this->thread->getData('last_update_milli');
        $raw_data['post_count'] = $this->thread->getData('post_count');
        $raw_data['bump_count'] = $this->thread->getData('post_count');
        $raw_data['total_uploads'] = $this->thread->getData('total_uploads');
        $raw_data['file_count'] = $this->thread->getData('file_count');
        $raw_data['embed_count'] = $this->thread->getData('embed_count');
        $raw_data['permasage'] = $this->thread->getData('permasage');
        $raw_data['sticky'] = $this->thread->getData('sticky');
        $raw_data['locked'] = $this->thread->getData('locked');
        $raw_data['cyclic'] = $this->thread->getData('cyclic');
        $raw_data['old'] = $this->thread->getData('old');
        $raw_data['shadow'] = $this->thread->getData('shadow');
        $raw_data['slug'] = $this->thread->getData('slug');
        $posts = $this->thread->getPosts();

        foreach ($posts as $post) {
            $raw_data['posts'][] = $post->getJSON()->getRawData();
        }

        $raw_data = nel_plugins()->processHook('nel-in-after-thread-json', [$this->thread], $raw_data);
        $this->raw_data = $raw_data;
        $this->needs_update = false;
    }
}