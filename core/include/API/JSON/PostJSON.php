<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Post;

class PostJSON extends JSON
{
    private Post $post;

    function __construct(Post $post)
    {
        $this->post = $post;
    }

    protected function generate(): void
    {
        $raw_data = array();
        $raw_data['post_number'] = $this->post->getData('post_number');
        $raw_data['parent_thread'] = $this->post->getData('parent_thread');
        $raw_data['reply_to'] = $this->post->getData('reply_to');

        if (!nel_true_empty($this->post->getData('name')) && $this->post->domain()->setting('show_poster_name')) {
            $raw_data['name'] = $this->post->getData('name');
        }

        if (!nel_true_empty($this->post->getData('tripcode')) && $this->post->domain()->setting('show_tripcodes')) {
            $raw_data['tripcode'] = $this->post->getData('tripcode');
        }

        if (!nel_true_empty($this->post->getData('secure_tripcode')) && $this->post->domain()->setting('show_tripcodes')) {
            $raw_data['secure_tripcode'] = $this->post->getData('secure_tripcode');
        }

        if (!nel_true_empty($this->post->getData('capcode')) && $this->post->domain()->setting('show_capcode')) {
            $raw_data['capcode'] = $this->post->getData('capcode');
        }

        if (!nel_true_empty($this->post->getData('email'))) {
            $raw_data['email'] = $this->post->getData('email');
        }

        if (!nel_true_empty($this->post->getData('subject')) && $this->post->domain()->setting('show_post_subject')) {
            $raw_data['subject'] = $this->post->getData('subject');
        }

        if (!nel_true_empty($this->post->getData('comment')) && $this->post->domain()->setting('show_user_comments')) {
            $raw_data['comment'] = $this->post->getData('comment');
        }

        $raw_data['post_time'] = $this->post->getData('post_time');
        $raw_data['post_time_milli'] = $this->post->getData('post_time_milli');
        $raw_data['formatted_time'] = $this->post->domain()->domainDateTime(intval($this->post->getData('post_time')))->format(
            $this->post->domain()->setting('post_time_format'));
        $raw_data['total_uploads'] = $this->post->getData('total_uploads');
        $raw_data['file_count'] = $this->post->getData('file_count');
        $raw_data['embed_count'] = $this->post->getData('embed_count');
        $raw_data['op'] = $this->post->getData('op');
        $raw_data['sage'] = $this->post->getData('sage');

        if (!nel_true_empty($this->post->getData('mod_comment')) && $this->post->domain()->setting('show_mod_comments')) {
            $raw_data['mod_comment'] = $this->post->getData('mod_comment');
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