<?php

namespace Nelliel;

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

    public abstract function loadFromDatabase($database = null);

    public abstract function writeToDatabase($database = null);

    public abstract function remove();

    public abstract function removeFromDatabase($database = null);

    public abstract function removeFromDisk();

    public abstract function updateCounts();
}