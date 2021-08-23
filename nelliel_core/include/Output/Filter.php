<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class Filter
{

    function __construct()
    {
    }

    public function cleanAndEncode(string &$string)
    {
        if (nel_true_empty($string))
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

    public function newlinesToArray(string $string)
    {
        $text_array = preg_split('#\r\n?|\n#', $string);
        return $text_array;
    }

    public function filterZalgo(string $text)
    {
        // https://stackoverflow.com/questions/32921751/how-to-prevent-zalgo-text-using-php
        // Modified slightly to accomodate valid uses of 2 phonetic marks
        return preg_replace('/(?:[\p{M}]{1})([\p{M}])+?/u', '', $text);
    }
}