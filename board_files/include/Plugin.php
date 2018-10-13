<?php

namespace Nelliel;

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
}