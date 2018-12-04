<?php

namespace Nelliel\API\JSON;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class JSONPost extends JSONBase
{
    private $board_id;

    function __construct($board_id)
    {
        $this->board_id = $board_id;
    }

    public function writeJSON()
    {
        ;
    }

    public function prepareData($data)
    {
        $post_array = array();
        $post_array['post_number'] = nel_cast_to_datatype($data['post_number'], 'integer');
        $post_array['parent_thread'] = nel_cast_to_datatype($data['parent_thread'], 'integer');
        $this->addIfNotEmpty($post_array, 'poster_name', $data['poster_name'], 'string');
        $this->addIfNotEmpty($post_array, 'tripcode', $data['tripcode'], 'string');
        $this->addIfNotEmpty($post_array, 'secure_tripcode', $data['secure_tripcode'], 'string');
        $this->addIfNotEmpty($post_array, 'email', $data['email'], 'string');
        $this->addIfNotEmpty($post_array, 'subject', $data['subject'], 'string');
        $this->addIfNotEmpty($post_array, 'comment', $data['comment'], 'string');
        $post_array['post_time'] = nel_cast_to_datatype($data['post_time'], 'integer');
        $post_array['post_time_milli'] = nel_cast_to_datatype($data['post_time_milli'], 'integer');
        $post_array['has_file'] = nel_cast_to_datatype($data['has_file'], 'boolean');
        $post_array['file_count'] = nel_cast_to_datatype($data['file_count'], 'integer');
        $post_array['op'] = nel_cast_to_datatype($data['op'], 'boolean');
        $post_array['sage'] = nel_cast_to_datatype($data['sage'], 'boolean');
        $this->addIfNotEmpty($post_array, 'mod_comment', $data['mod_comment'], 'string');
        return $post_array;
    }
}