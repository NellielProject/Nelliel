<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_is_in_string($string, $substring)
{
    return utf8_strripos($string, $substring) !== false;
}

function nel_clean_exit($redirect = false)
{
    $authorize = nel_authorize();
    $authorize->save_users();
    $authorize->save_roles();
    $authorize->save_user_roles();

    if($redirect)
    {
        echo '<meta http-equiv="refresh" content="2;URL=' . nel_board_references(INPUT_BOARD_ID, 'board_directory') . '/' .
        PHP_SELF2 . PHP_EXT . '">';
    }

    die();
}

function get_millisecond_time()
{
    $time = explode(' ', microtime());
    $time[0] = str_pad(round($time[0] * 1000), 3, '0', STR_PAD_LEFT);
    return $time[1] . $time[0];
}

//
// PHP's empty() does typecasting and treats 0 or false as empty when they are still technically values.
// true_empty() checks that there is no actual value present, only an empty or unset variable.
//
function true_empty($var)
{
    if (!empty($var))
    {
        return false;
    }

    return !isset($var) || is_null($var) || (is_string($var) && $var === '') || (is_array($var) && $var === array());
}

function nel_utf8_to_numeric_html_entities(&$input, $non_ascii_only = true)
{
    $regex = ($non_ascii_only) ? '#([^[:ascii:]])#Su' : '#(.)#Su';

    $input = preg_replace_callback($regex, function ($matches)
    {
        return '&#' . utf8_ord($matches[0]) . ';';
    }, $input);
}

function nel_numeric_html_entities_to_utf8(&$input)
{
    $input = preg_replace_callback('#&\#[0-9]+;#Su', function ($matches)
    {
        return utf8_chr(intval(substr($matches[0], 2, -1)));
    }, $input);
}