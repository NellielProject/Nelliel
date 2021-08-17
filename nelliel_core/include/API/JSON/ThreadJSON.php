<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Thread;
use Nelliel\Utility\FileHandler;

class ThreadJSON extends JSON
{
    protected $posts = array();

    function __construct(Thread $thread, FileHandler $file_handler)
    {
        $this->file_handler = $file_handler;
        $this->source = $thread;
    }

    public function generate(): void
    {
        $this->raw_data['api_version'] = $this->api_version;
        $this->raw_data['thread_id'] = $this->source->data('thread_id');
        $this->raw_data['last_bump_time'] = $this->source->data('last_bump_time');
        $this->raw_data['last_bump_time_milli'] = $this->source->data('last_bump_time_milli');
        $this->raw_data['last_update'] = $this->source->data('last_update');
        $this->raw_data['last_update_milli'] = $this->source->data('last_update_milli');
        $this->raw_data['post_count'] = $this->source->data('post_count');
        $this->raw_data['total_uploads'] = $this->source->data('total_uploads');
        $this->raw_data['file_count'] = $this->source->data('file_count');
        $this->raw_data['embed_count'] = $this->source->data('embed_count');
        $this->raw_data['permasage'] = $this->source->data('permasage');
        $this->raw_data['sticky'] = $this->source->data('sticky');
        $this->raw_data['locked'] = $this->source->data('locked');
        $this->raw_data['cyclic'] = $this->source->data('cyclic');

        foreach ($this->posts as $post)
        {
            $this->raw_data['posts'][] = $post->getRawData();
        }

        $this->json = json_encode($this->raw_data);
        $this->generated = true;
    }

    public function write(): void
    {
        $filename = $this->source->contentID()->threadID() . NEL_JSON_EXT;
        $this->file_handler->writeFile($this->source->pagePath() . $filename, $this->getJSON());
    }

    public function addPost(PostJSON $post): void
    {
        $this->posts[] = $post;
    }
}