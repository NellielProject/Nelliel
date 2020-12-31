<?php

namespace SmallPHPGettext;

class Helpers
{
    private $category_lookups = [0 => 'LC_ALL', 1 => 'LC_COLLATE', 2 => 'LC_CTYPE', 3 => 'LC_MONETARY',
        4 => 'LC_NUMERIC', 5 => 'LC_TIME', 6 => 'LC_MESSAGES', 'LC_ALL' => 0, 'LC_COLLATE' => 1, 'LC_CTYPE' => 2,
        'LC_MONETARY' => 3, 'LC_NUMERIC' => 4, 'LC_TIME' => 5, 'LC_MESSAGES' => 6];

    function __construct()
    {
    }

    public function categoryToString(int $category)
    {
        if (isset($this->category_lookups[$category]))
        {
            return $this->category_lookups[$category];
        }

        return false;
    }

    public function categoryFromString(string $category)
    {
        if (isset($this->category_lookups[$category]))
        {
            return $this->category_lookups[$category];
        }

        return false;
    }

    public function poToString(string $string)
    {
        $string = preg_replace_callback('/(?<!\\\)(\\\[nrtvef])/u',
                function ($match)
                {
                    $conversions = ['\n' => "\n", '\r' => "\r", '\t' => "\t", '\v' => "\v", '\e' => "\e", '\f' => "\f"];
                    return strtr($match[0], $conversions);
                }, $string);
        $conversions = ['\\\\' => '\\', '\\' => ''];
        return strtr($string, $conversions);
    }

    public function unquoteLine(string $string)
    {
        return preg_replace('/^"|"\s*?$/u', '', $string);
    }

    public function stringToPo(string $string)
    {
        $conversions = ['\\' => '\\\\', "\n" => '\n', "\t" => '\t', "\"" => '\\"'];
        return strtr($string, $conversions);
    }
}