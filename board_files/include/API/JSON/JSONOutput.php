<?php

namespace Nelliel\API\JSON;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class JSONOutput
{
    protected $api_version = 0;
    protected $data_array = array();
    protected $file_handler;
    protected $domain;

    public abstract function prepareData(array $data);

    protected function setVersion()
    {
        if(!isset($this->data_array['version']['api_version']))
        {
            $this->data_array['version'] = ['api_version' => $this->api_version];
        }
    }

    public function storeData(array $data, string $sub_section = null)
    {
        if(is_null($sub_section))
        {
            $this->data_array = $data;
        }
        else
        {
            $this->data_array[$sub_section] = $data;
        }
    }

    public function retrieveData(string $sub_section = null)
    {
        if(is_null($sub_section))
        {
            return $this->data_array;
        }
        else
        {
            return $this->data_array[$sub_section];
        }
    }

    public function writeStoredData($path, $filename)
    {
        $this->setVersion();
        $json_data = json_encode($this->data_array);
        $this->file_handler->writeFile($path . $filename . JSON_EXT, $json_data);
    }

    protected function addIfNotEmpty(&$data, $key, $value, $type)
    {
        if ($value === null)
        {
            return;
        }

        if ($type === 'string' && $value !== '')
        {
            $data[$key] = nel_cast_to_datatype($value, $type);
        }

        if ($type === 'integer' && $value !== 0)
        {
            $data[$key] = nel_cast_to_datatype($value, $type);
        }
    }
}