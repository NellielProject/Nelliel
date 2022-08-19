<?php

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Post;

class PostJSON extends JSON
{
    protected $uploads = array();

    function __construct(Post $post = null)
    {
        if (!is_null($post)) {
            $this->generateFromContent($post);
        }
    }

    public function generateFromContent(Post $post): void
    {
        $this->raw_data['post_number'] = $post->data('post_number');
        $this->raw_data['parent_thread'] = $post->data('parent_thread');
        $this->raw_data['reply_to'] = $post->data('reply_to');
        $this->raw_data['name'] = $post->data('name');
        $this->raw_data['capcode'] = $post->data('capcode');
        $this->raw_data['tripcode'] = $post->data('tripcode');
        $this->raw_data['secure_tripcode'] = $post->data('secure_tripcode');
        $this->raw_data['email'] = $post->data('email');
        $this->raw_data['subject'] = $post->data('subject');
        $this->raw_data['comment'] = $post->data('comment');
        $this->raw_data['post_time'] = $post->data('post_time');
        $this->raw_data['post_time_milli'] = $post->data('post_time_milli');
        $this->raw_data['formatted_time'] = date($post->domain()->setting('date_format'), $post->data('post_time'));
        $this->raw_data['total_uploads'] = $post->data('total_uploads');
        $this->raw_data['file_count'] = $post->data('file_count');
        $this->raw_data['embed_count'] = $post->data('embed_count');
        $this->raw_data['op'] = $post->data('op');
        $this->raw_data['sage'] = $post->data('sage');
        $this->raw_data['mod_comment'] = $post->data('mod_comment');
        $this->raw_data['uploads'] = array();
        $this->generate();
    }

    protected function generate(): void
    {
        foreach ($this->uploads as $upload) {
            $this->raw_data['uploads'][] = $upload->getRawData();
        }

        $this->json = json_encode($this->raw_data);
        $this->json_needs_update = false;
    }

    public function addUpload(UploadJSON $upload): void
    {
        $this->uploads[] = $upload;
        $this->json_needs_update = true;
    }
}