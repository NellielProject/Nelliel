<?php

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\ContentPost;
use Nelliel\Utility\FileHandler;

class PostJSON extends JSON
{
    protected $uploads = array();

    function __construct(ContentPost $post, FileHandler $file_handler)
    {
        $this->file_handler = $file_handler;
        $this->source = $post;
    }

    public function generate(): void
    {
        $this->raw_data['post_number'] = $this->source->data('post_number');
        $this->raw_data['parent_thread'] = $this->source->data('parent_thread');
        $this->raw_data['reply_to'] = $this->source->data('reply_to');
        $this->raw_data['name'] = $this->source->data('name');
        $this->raw_data['capcode'] = $this->source->data('capcode');
        $this->raw_data['tripcode'] = $this->source->data('tripcode');
        $this->raw_data['secure_tripcode'] = $this->source->data('secure_tripcode');
        $this->raw_data['email'] = $this->source->data('email');
        $this->raw_data['subject'] = $this->source->data('subject');
        $this->raw_data['comment'] = $this->source->data('comment');
        $this->raw_data['post_time'] = $this->source->data('post_time');
        $this->raw_data['post_time_milli'] = $this->source->data('post_time_milli');
        $this->raw_data['formatted_time'] = date($this->source->domain()->setting('date_format'),
                $this->source->data('post_time'));
        $this->raw_data['has_uploads'] = $this->source->data('has_uploads');
        $this->raw_data['total_uploads'] = $this->source->data('total_uploads');
        $this->raw_data['file_count'] = $this->source->data('file_count');
        $this->raw_data['embed_count'] = $this->source->data('embed_count');
        $this->raw_data['op'] = $this->source->data('op');
        $this->raw_data['sage'] = $this->source->data('sage');
        $this->raw_data['mod_comment'] = $this->source->data('mod_comment');
        $this->raw_data['uploads'] = array();

        foreach ($this->uploads as $upload)
        {
            $this->raw_data['uploads'][] = $upload->getRawData();
        }

        $this->json = json_encode($this->raw_data);
        $this->generated = true;
    }

    public function write(): void
    {
        ;
    }

    public function addUpload(UploadJSON $upload): void
    {
        $this->uploads[] = $upload;
    }
}