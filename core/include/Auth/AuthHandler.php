<?php

declare(strict_types=1);

namespace Nelliel\Auth;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

abstract class AuthHandler
{
    protected $database;
    protected $auth_data = array();
    protected $auth_id;
    protected $authorization;
    protected $empty = false;
    protected $changed = false;

    public function authDataOrDefault(string $key, $default = null)
    {
        return $this->getData($key) ?? $default;
    }

    public abstract function loadFromDatabase(): bool;

    public abstract function writeToDatabase(): bool;

    public abstract function exists(): bool;

    public abstract function remove(): void;

    public function id()
    {
        return $this->auth_id;
    }

    public function empty(): bool
    {
        return $this->empty;
    }

    public function changed(): bool
    {
        return $this->changed;
    }

    public function getData(string $key)
    {
        return $this->auth_data[$key] ?? null;
    }

    public function changeData(string $key, $value): void
    {
        $this->changed = true;
        $this->auth_data[$key] = $value;
    }
}

