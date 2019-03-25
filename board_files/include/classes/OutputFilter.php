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

    public function cleanAndEncode(&$string)
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

    public function clearWhitespace(&$string)
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

    public function newlinesToArray($string)
    {
        $text_array = preg_split('#\r\n?|\n#', $string);
        return $text_array;
    }

    public function postQuote($target_element, $text_input, $return_text = false)
    {
        if($return_text)
        {
            return '<span class="post-quote">' . $text_input . '</span>';
        }

        $segment_node = $target_element->ownerDocument->createElement('span', $text_input);
        $segment_node->extSetAttribute('class', 'post-quote');
        return $segment_node;
    }
}