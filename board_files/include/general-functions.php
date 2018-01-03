<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_is_in_string($string, $substring)
{
    if (utf8_strripos($string, $substring) !== false)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function nel_clean_exit($dataforce, $die)
{
    $authorize = nel_authorize();
    $authorize->save_users();
    $authorize->save_roles();

    if ($die)
    {
        die();
    }

    echo '<meta http-equiv="refresh" content="2;URL=' . PHP_SELF2 . PHP_EXT . '">';
    die();
}

function get_millisecond_time()
{
    $time = explode(' ', microtime());
    $time[0] = str_pad(round($time[0] * 1000), 3, '0', STR_PAD_LEFT);
    return $time[1] . $time[0];
}

function nel_true_empty($var)
{
    if (!empty($var))
    {
        return false;
    }

    if(is_null($var) || (is_string($var) && $var === '') || (is_array($var) && $var === array()))
    {
        return true;
    }

    return false;
}

function nel_utf8_to_numeric_html_entities(&$input, $non_ascii_only = true)
{
    if($non_ascii_only)
    {
        $regex = '#([^[:ascii:]])#Su';
    }
    else
    {
        $regex = '#(.)#Su';
    }

    $input = preg_replace_callback($regex,
    function ($matches)
    {
        return '&#' . utf8_ord($matches[0]). ';';
    }, $input);
}

function nel_numeric_html_entities_to_utf8(&$input)
{
    $input = preg_replace_callback('#&\#[0-9]+;#Su',
    function ($matches)
    {
        return utf8_chr(intval(substr($matches[0], 2, -1)));
    }, $input);
}