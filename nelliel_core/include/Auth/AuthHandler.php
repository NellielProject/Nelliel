<?php

namespace Nelliel\Auth;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class AuthHandler
{
    public $database;
    public $auth_data = array();
    public $auth_id;
    public $authorization;
    private $empty;

    public function authDataOrDefault(string $data_name, ?string $default = null)
    {
        return $this->auth_data[$data_name] ?? $default;
    }

    public abstract function loadFromDatabase($temp_database = null);

    public abstract function writeToDatabase($temp_database = null);

    public abstract function setupNew();

    public abstract function remove();

    public function getInfo(string $info_id)
    {
        return $this->auth_data[$info_id] ?? null;
    }

    public function id()
    {
        return $this->auth_id;
    }

    public function empty()
    {
        return $this->empty;
    }
}

