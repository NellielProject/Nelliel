<?php

namespace Nelliel\Content;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Moar;

abstract class ContentHandler
{
    protected $content_id;
    protected $database;
    protected $domain;
    protected $content_data = array();
    protected $content_moar;

    public abstract function loadFromDatabase();

    public abstract function writeToDatabase();

    public abstract function remove();

    protected abstract function removeFromDatabase();

    protected abstract function removeFromDisk();

    public abstract function updateCounts();

    protected abstract function verifyModifyPerms();

    public function storeMoar(Moar $moar)
    {
        $this->content_moar = $moar;
    }

    public function getMoar()
    {
        return $this->content_moar;
    }

    protected function contentDataOrDefault(string $data_name, $default)
    {
        if (isset($this->content_data[$data_name]))
        {
            return $this->content_data[$data_name];
        }

        return $default;
    }

    protected function dataLoaded(bool $load = false)
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

    public function contentID()
    {
        return $this->content_id;
    }

    public function data(string $key)
    {
        return $this->content_data[$key] ?? null;
    }

    public function changeData(string $key, $new_data)
    {
        $old_data = $this->data($key);
        $this->content_data[$key] = $new_data;
        return $old_data;
    }
}