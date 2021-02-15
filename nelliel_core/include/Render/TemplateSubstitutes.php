<?php

declare(strict_types=1);

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class TemplateSubstitutes
{
    protected $substitutes = array();

    function __construct()
    {
    }

    public function add(string $template, $substitute)
    {
        $this->substitutes[$template] = $substitute;
    }

    public function get(string $template)
    {
        return $this->substitutes[$template] ?? $template;
    }

    public function remove(string $template)
    {
        unset($this->substitutes[$template]);
    }

    public function isSubstituted(string $template)
    {
        return $this->get($template) !== $template;
    }

    public function getAll()
    {
        return $this->substitutes;
    }

    public function clear()
    {
        $this->substitutes = array();
    }
}