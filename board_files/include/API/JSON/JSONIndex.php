<?php

namespace Nelliel\API\JSON;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class JSONIndex extends JSONBase
{

    function __construct($domain, $file_handler)
    {
        $this->domain = $domain;
        $this->file_handler = $file_handler;
    }

    public function prepareData($data, $store = false)
    {
        $index_array = array();
        $index_array = nel_plugins()->processHook('nel-json-prepare-post', array($data), $index_array);

        if($store)
        {
            $this->data_array = $index_array;
        }
        else
        {
            return $index_array;
        }
    }

    public function storeData($data)
    {
        $this->data_array = $data;
    }

    public function getStoredData()
    {
        return $this->data_array;
    }

    public function writeStoredData($file_path, $file_name)
    {
        $json_data = json_encode($this->data_array);
        $this->file_handler->writeFile($file_path . $file_name . JSON_EXT, $json_data);
    }

    public function addThreadData($thread_data)
    {
        $this->data_array['thread-list'][] = $thread_data;
    }
}