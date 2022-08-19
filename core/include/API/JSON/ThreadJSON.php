<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Thread;

class ThreadJSON extends JSON
{
    protected $posts = array();

    function __construct(Thread $thread = null)
    {
        if (!is_null($thread)) {
            $this->generateFromContent($thread);
        }
    }

    public function generateFromContent(Thread $thread): void
    {
        $this->raw_data['api_version'] = $this->api_version;
        $this->raw_data['thread_id'] = $thread->data('thread_id');
        $this->raw_data['bump_time'] = $thread->data('bump_time');
        $this->raw_data['bump_time_milli'] = $thread->data('bump_time_milli');
        $this->raw_data['last_update'] = $thread->data('last_update');
        $this->raw_data['last_update_milli'] = $thread->data('last_update_milli');
        $this->raw_data['post_count'] = $thread->data('post_count');
        $this->raw_data['total_uploads'] = $thread->data('total_uploads');
        $this->raw_data['file_count'] = $thread->data('file_count');
        $this->raw_data['embed_count'] = $thread->data('embed_count');
        $this->raw_data['permasage'] = $thread->data('permasage');
        $this->raw_data['sticky'] = $thread->data('sticky');
        $this->raw_data['locked'] = $thread->data('locked');
        $this->raw_data['cyclic'] = $thread->data('cyclic');
        $this->generate();
    }

    protected function generate(): void
    {
        foreach ($this->posts as $post) {
            $this->raw_data['posts'][] = $post->getRawData();
        }

        $this->json = json_encode($this->raw_data);
        $this->json_needs_update = false;
    }

    public function addPost(PostJSON $post): void
    {
        $this->posts[] = $post;
        $this->json_needs_update = true;
    }
}