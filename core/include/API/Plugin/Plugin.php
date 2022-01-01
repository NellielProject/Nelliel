<?php

declare(strict_types=1);

namespace Nelliel\API\Plugin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class Plugin
{
    private $id;
    private $ini;
    private $directory;
    private $initializer;

    function __construct(string $id, string $directory, array $ini)
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