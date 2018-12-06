<?php

namespace Nelliel\API\JSON;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class JSONThread extends JSONBase
{
    private $board_id;
    private $thread_id;
    private $json_post;
    private $json_content;

    function __construct($board_id, $file_handler, $thread_id)
    {
        $this->board_id = $board_id;
        $this->file_handler = $file_handler;
        $board_path = nel_parameters_and_data()->boardReferences($board_id, 'board_path');
        $page_path = nel_parameters_and_data()->boardReferences($board_id, 'page_path') . $thread_id . '/';
        $this->file_path = $page_path;
        $filename_format = nel_parameters_and_data()->siteSettings('thread_filename_format');
        $this->file_name = sprintf('thread-%d', $thread_id);
        $this->json_post = new JSONPost($this->board_id);
        $this->json_content = new JSONContent($this->board_id);
    }

    public function writeJSON()
    {
        $json_data = json_encode($this->data_array);
        $this->file_handler->writeFile($this->file_path . $this->file_name . JSON_EXT, $json_data);
    }

    public function prepareData($data)
    {
        $thread_array = array();
        $thread_array['thread_id'] = nel_cast_to_datatype($data['thread_id'], 'integer');
        $thread_array['first_post'] = nel_cast_to_datatype($data['first_post'], 'integer');
        $thread_array['last_post'] = nel_cast_to_datatype($data['last_post'], 'integer');
        $thread_array['total_files'] = nel_cast_to_datatype($data['total_files'], 'integer');
        $thread_array['last_update'] = nel_cast_to_datatype($data['last_update'], 'integer');
        $thread_array['last_update_milli'] = nel_cast_to_datatype($data['last_update_milli'], 'integer');
        $thread_array['post_count'] = nel_cast_to_datatype($data['post_count'], 'integer');
        $thread_array['thread_sage'] = nel_cast_to_datatype($data['thread_sage'], 'boolean');
        $thread_array['sticky'] = nel_cast_to_datatype($data['sticky'], 'boolean');
        $thread_array['locked'] = nel_cast_to_datatype($data['locked'], 'boolean');
        return $thread_array;
    }

    public function storeData($data)
    {
        $this->data_array = $data;
    }

    public function getStoredData()
    {
        return $this->data_array;
    }

    public function addThreadData($thread_data)
    {
        $this->data_array['thread'] = $thread_data;
    }

    public function addPostData($post_data)
    {
        $this->data_array['posts'][] = $post_data;
    }
}