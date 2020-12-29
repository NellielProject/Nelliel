<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Filter
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

    public function filterUnicodeCombiningCharacters(string $text)
    {
        $text = preg_replace_callback('#([^[:ascii:]])#Su', function ($matches)
        {
            $character = $matches[0];
            $ordinal = utf8_ord($matches[0]);

            // Base
            if($ordinal >= 768 && $ordinal <= 879)
            {
                $character = '';
            }

            // Extended
            if($ordinal >= 6832 && $ordinal <= 6911)
            {
                $character = '';
            }

            // Supplement
            if($ordinal >= 7616 && $ordinal <= 7679)
            {
                $character = '';
            }

            // Symbols
            if($ordinal >= 8400 && $ordinal <= 8447)
            {
                $character = '';
            }

            // Half Marks
            if($ordinal >= 65056 && $ordinal <= 65071)
            {
                $character = '';
            }

            return $character;
        }, $text);

        return $text;
    }
}