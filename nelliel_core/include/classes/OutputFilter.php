<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class OutputFilter
{

    function __construct()
    {
    }

    public function cleanAndEncode(string &$string)
    {
        if(empty($string))
        {
            return;
        }

        if (preg_match("#^\s*$#", $string))
        {
            $string = '';
        }

        $string = trim($string);
        $string = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
    }

    public function clearWhitespace(string &$string)
    {
        if(empty($string))
        {
            return;
        }

        if (ctype_space($string))
        {
            $string = '';
        }
    }

    public function newlinesToArray(string $string)
    {
        $text_array = preg_split('#\r\n?|\n#', $string);
        return $text_array;
    }
}