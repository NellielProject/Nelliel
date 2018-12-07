<?php

namespace Nelliel\Content;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class ContentBase
{
    public $content_id;
    public $database;
    public $board_id;
    public $content_data = array();

    public function contentDataOrDefault($data_name, $default)
    {
        if (isset($this->content_data[$data_name]))
        {
            return $this->content_data[$data_name];
        }

        return $default;
    }

    public function dataIsLoaded($load = false)
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

    public abstract function loadFromDatabase($temp_database = null);

    public abstract function writeToDatabase($temp_database = null);

    public abstract function remove();

    protected abstract function removeFromDatabase($temp_database = null);

    protected abstract function removeFromDisk();

    public abstract function updateCounts();

    public abstract function verifyModifyPerms();
}