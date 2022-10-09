<?php

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Post;

class PostJSON extends JSON
{
    private $post;

    function __construct(Post $post)
    {
        $this->post = $post;
    }

    protected function generate(): void
    {
        $this->raw_data = array();
        $this->raw_data['post_number'] = $this->post->data('post_number');
        $this->raw_data['parent_thread'] = $this->post->data('parent_thread');
        $this->raw_data['reply_to'] = $this->post->data('reply_to');
        $this->raw_data['name'] = $this->post->data('name');
        $this->raw_data['capcode'] = $this->post->data('capcode');
        $this->raw_data['tripcode'] = $this->post->data('tripcode');
        $this->raw_data['secure_tripcode'] = $this->post->data('secure_tripcode');
        $this->raw_data['email'] = $this->post->data('email');
        $this->raw_data['subject'] = $this->post->data('subject');
        $this->raw_data['comment'] = $this->post->data('comment');
        $this->raw_data['post_time'] = $this->post->data('post_time');
        $this->raw_data['post_time_milli'] = $this->post->data('post_time_milli');
        $this->raw_data['formatted_time'] = date($this->post->domain()->setting('post_date_format'),
            $this->post->data('post_time'));
        $this->raw_data['total_uploads'] = $this->post->data('total_uploads');
        $this->raw_data['file_count'] = $this->post->data('file_count');
        $this->raw_data['embed_count'] = $this->post->data('embed_count');
        $this->raw_data['op'] = $this->post->data('op');
        $this->raw_data['sage'] = $this->post->data('sage');
        $this->raw_data['mod_comment'] = $this->post->data('mod_comment');
        $uploads = $this->post->getUploads();

        foreach ($uploads as $upload) {
            $this->raw_data['uploads'][] = $upload->getJSON()->getRawData();
        }

        $this->json = json_encode($this->raw_data);
        $this->needs_update = false;
    }
}