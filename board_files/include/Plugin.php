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

    function __construct($id, $directory)
    {
        $this->id = $id;
        $this->directory = $directory;
        $this->ini = parse_ini_file($directory . '/nelliel-plugin.ini');
    }
}