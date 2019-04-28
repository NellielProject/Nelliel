<?php

namespace Nelliel\API\JSON;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\FileHandler;

class JSONPost extends JSONOutput
{

    function __construct(Domain $domain, FileHandler $file_handler)
    {
        $this->domain = $domain;
        $this->file_handler = $file_handler;
        $this->data_array['post'] = array();
    }

    public function prepareData(array $data)
    {
        $authorization = new \Nelliel\Auth\Authorization($this->domain->database());
        $post_array = array();
        $post_array['post_number'] = nel_cast_to_datatype($data['post_number'], 'integer');
        $post_array['parent_thread'] = nel_cast_to_datatype($data['parent_thread'], 'integer');
        $this->addIfNotEmpty($post_array, 'reply_to', $data['reply_to'], 'integer');
        $this->addIfNotEmpty($post_array, 'poster_name', $data['poster_name'], 'string');
        $this->addIfNotEmpty($post_array, 'tripcode', $data['tripcode'], 'string');
        $this->addIfNotEmpty($post_array, 'secure_tripcode', $data['secure_tripcode'], 'string');
        $capcode_text = (!empty($data['mod_post_id'])) ? $authorization->getRole($data['mod_post_id'])->auth_data['capcode_text'] : '';
        $this->addIfNotEmpty($post_array, 'capcode_text', $capcode_text, 'string');
        $this->addIfNotEmpty($post_array, 'email', $data['email'], 'string');
        $this->addIfNotEmpty($post_array, 'subject', $data['subject'], 'string');
        $this->addIfNotEmpty($post_array, 'comment', $data['comment'], 'string');
        $post_array['post_time'] = nel_cast_to_datatype($data['post_time'], 'integer');
        $post_array['post_time_milli'] = nel_cast_to_datatype($data['post_time_milli'], 'integer');
        $post_array['timestamp'] = date($this->domain->setting('date_format'), $data['post_time']);
        $post_array['has_content'] = nel_cast_to_datatype($data['has_content'], 'boolean');
        $post_array['content_count'] = nel_cast_to_datatype($data['content_count'], 'integer');
        $post_array['op'] = nel_cast_to_datatype($data['op'], 'boolean');
        $post_array['sage'] = nel_cast_to_datatype($data['sage'], 'boolean');
        $this->addIfNotEmpty($post_array, 'mod_comment', $data['mod_comment'], 'string');
        $post_array = nel_plugins()->processHook('nel-json-prepare-post', [$data], $post_array);
        return $post_array;
    }

    public function addContentData(array $content_data)
    {
        $this->data_array['content-list'][] = $content_data;
    }
}