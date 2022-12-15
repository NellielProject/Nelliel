<?php
declare(strict_types = 1);

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
        $raw_data = array();
        $raw_data['post_number'] = $this->post->data('post_number');
        $raw_data['parent_thread'] = $this->post->data('parent_thread');
        $raw_data['reply_to'] = $this->post->data('reply_to');

        if (!nel_true_empty($this->post->data('name'))) {
            $raw_data['name'] = $this->post->data('name');
        }

        if (!nel_true_empty($this->post->data('tripcode'))) {
            $raw_data['tripcode'] = $this->post->data('tripcode');
        }

        if (!nel_true_empty($this->post->data('secure_tripcode'))) {
            $raw_data['secure_tripcode'] = $this->post->data('secure_tripcode');
        }

        if (!nel_true_empty($this->post->data('capcode'))) {
            $raw_data['capcode'] = $this->post->data('capcode');
        }

        if (!nel_true_empty($this->post->data('email'))) {
            $raw_data['email'] = $this->post->data('email');
        }

        if (!nel_true_empty($this->post->data('subject'))) {
            $raw_data['subject'] = $this->post->data('subject');
        }

        if (!nel_true_empty($this->post->data('comment'))) {
            $raw_data['comment'] = $this->post->data('comment');
        }

        $raw_data['post_time'] = $this->post->data('post_time');
        $raw_data['post_time_milli'] = $this->post->data('post_time_milli');
        $raw_data['formatted_time'] = date($this->post->domain()->setting('post_date_format'),
            (int) $this->post->data('post_time'));
        $raw_data['total_uploads'] = $this->post->data('total_uploads');
        $raw_data['file_count'] = $this->post->data('file_count');
        $raw_data['embed_count'] = $this->post->data('embed_count');
        $raw_data['op'] = $this->post->data('op');
        $raw_data['sage'] = $this->post->data('sage');

        if (!nel_true_empty($this->post->data('mod_comment'))) {
            $raw_data['mod_comment'] = $this->post->data('mod_comment');
        }

        $uploads = $this->post->getUploads();

        foreach ($uploads as $upload) {
            $raw_data['uploads'][] = $upload->getJSON()->getRawData();
        }

        $raw_data = nel_plugins()->processHook('nel-in-after-post-json', [$this->post], $raw_data);
        $this->raw_data = $raw_data;
        $this->needs_update = false;
    }
}