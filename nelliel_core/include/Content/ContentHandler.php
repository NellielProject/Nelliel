<?php

namespace Nelliel\Content;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class ContentHandler
{
    public $content_id;
    public $database;
    public $board_id;
    public $domain;
    public $content_data = array();

    public abstract function loadFromDatabase($temp_database = null);

    public abstract function writeToDatabase($temp_database = null);

    public abstract function remove();

    protected abstract function removeFromDatabase($temp_database = null);

    protected abstract function removeFromDisk();

    public abstract function updateCounts();

    public abstract function verifyModifyPerms();

    protected function contentDataOrDefault(string $data_name, $default)
    {
        if (isset($this->content_data[$data_name]))
        {
            return $this->content_data[$data_name];
        }

        return $default;
    }

    protected function dataIsLoaded(bool $load = false)
    {
        if(empty($this->content_data))
        {
            if($load)
            {
                return $this->loadFromDatabase();
            }
            else
            {
                return false;
            }
        }

        return true;
    }
}