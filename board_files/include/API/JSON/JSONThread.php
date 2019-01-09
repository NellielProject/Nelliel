<?php

namespace Nelliel\API\JSON;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class JSONThread extends JSONOutput
{

    function __construct($domain, $file_handler)
    {
        $this->domain = $domain;
        $this->file_handler = $file_handler;
        $this->data_array['thread'] = array();
    }

    public function prepareData($data, $store = false)
    {
        $thread_array = array();
        $thread_array['thread_id'] = nel_cast_to_datatype($data['thread_id'], 'integer');
        $this->addIfNotEmpty($thread_array, 'first_post', $data['first_post'], 'integer');
        $this->addIfNotEmpty($thread_array, 'last_post', $data['last_post'], 'integer');
        $thread_array['last_bump_time'] = nel_cast_to_datatype($data['last_bump_time'], 'integer');
        $thread_array['last_bump_time_milli'] = nel_cast_to_datatype($data['last_bump_time_milli'], 'integer');
        $thread_array['last_update'] = nel_cast_to_datatype($data['last_update'], 'integer');
        $thread_array['last_update_milli'] = nel_cast_to_datatype($data['last_update_milli'], 'integer');
        $thread_array['post_count'] = nel_cast_to_datatype($data['post_count'], 'integer');
        $thread_array['total_files'] = nel_cast_to_datatype($data['total_files'], 'integer');
        $thread_array['thread_sage'] = nel_cast_to_datatype($data['thread_sage'], 'boolean');
        $thread_array['sticky'] = nel_cast_to_datatype($data['sticky'], 'boolean');
        $thread_array['locked'] = nel_cast_to_datatype($data['locked'], 'boolean');
        $this->addIfNotEmpty($thread_array, 'slug', $data['slug'], 'string');
        $thread_array = nel_plugins()->processHook('nel-json-prepare-thread', array($data), $thread_array);

        if ($store)
        {
            $this->data_array['thread'] = $thread_array;
        }

        return $thread_array;
    }

    public function storeData($data)
    {
        $this->data_array['thread'] = $data;
    }

    public function retrieveData($all_data = false)
    {
        if($all_data)
        {
            return $this->data_array;
        }
        else
        {
            return $this->data_array['thread'];
        }
    }

    public function writeStoredData($file_path, $file_name)
    {
        $json_data = json_encode($this->data_array);
        $this->file_handler->writeFile($file_path . $file_name . JSON_EXT, $json_data);
    }

    public function addPostData($post_data)
    {
        $this->data_array['post-list'][] = $post_data;
    }
}