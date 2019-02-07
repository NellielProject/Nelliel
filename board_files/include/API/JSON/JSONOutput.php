<?php

namespace Nelliel\API\JSON;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class JSONOutput
{
    protected $data_array = array();
    protected $file_handler;
    protected $domain;

    public abstract function prepareData(array $data, bool $store = false);

    public abstract function storeData(array $data);

    public abstract function retrieveData(bool $all_data = false);

    public abstract function writeStoredData($path, $filename);

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