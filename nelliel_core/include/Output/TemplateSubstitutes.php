<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class TemplateSubstitutes
{
    private static $substitutes = array();

    function __construct()
    {
    }

    public function add(string $template, $substitute)
    {
        self::$substitutes[$template] = $substitute;
    }

    public function get(string $template)
    {
        return self::$substitutes[$template] ?? $template;
    }

    public function isSubstituted(string $template)
    {
        return $this->get($template) !== $template;
    }

    public function getFunction(string $template, bool $partial = false)
    {
        $part = $partial ? '>' : '';

        return function () use ($part, $template)
        {
            return '{{' . $part . ' ' . $this->get($template) . ' }}';
        };
    }
}