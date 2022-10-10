<?php

namespace Nelliel\API\JSON\_4Chan;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\API\JSON\JSON;
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
        // 4chan specific (Nelliel does not implement, or does differently):
        // tag
        // since4pass
        // m_img
        // custom_spoiler

        // TODO: implement (and related functions if needed)
        // country (ISO)
        // country_name
        // board_flag
        // flag_name
        $uploads = $this->post->getUploads();
        $upload_count = count($uploads);
        $this->raw_data = array();
        $this->raw_data['no'] = $this->post->data('post_number');
        $this->raw_data['now'] = date($this->post->domain()->setting('post_date_format'), $this->post->data('post_time'));
        $this->raw_data['resto'] = $this->post->data('reply_to');
        $this->raw_data['name'] = $this->post->data('name');

        if (!nel_true_empty($this->post->data('tripcode'))) {
            $this->raw_data['trip'] = $this->post->data('tripcode');
        }

        if (!nel_true_empty($this->post->data('secure_tripcode'))) {
            $this->raw_data['trip'] = $this->post->data('secure_tripcode');
        }

        if (!nel_true_empty($this->post->data('capcode'))) {
            $this->raw_data['capcode'] = $this->post->data('capcode');
        }

        // $this->raw_data['id'] = ''; // Not implemented to only 8 chars

        if ($this->post->data('op') && !nel_true_empty($this->post->data('subject'))) {
            $this->raw_data['sub'] = $this->post->data('subject');
        }

        $this->raw_data['com'] = $this->post->getCache()['comment_markup'] ?? $this->post->data('comment');
        $this->raw_data['time'] = $this->post->data('post_time');

        if ($upload_count > 0) {
            $this->raw_data = $this->raw_data + $uploads[0]->getJSON()->getRawData();
        }

        $this->json = json_encode($this->raw_data);
        $this->needs_update = false;
    }
}