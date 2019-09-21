<?php

namespace Nelliel\API\Plugin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Plugin
{
    private $id;
    private $ini;
    private $directory;
    private $initializer;

    function __construct($id, $directory, $ini)
    {
        $this->id = $id;
        $this->directory = new \SplFileInfo($directory);
        $this->ini = $ini;
    }

    public function getIniValue(string $value_id)
    {
        return $this->ini[$value_id] ?? null;
    }
}