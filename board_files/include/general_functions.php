<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_clean_exit($redirect = false, $redirect_board = null, $redirect_delay = 2)
{
    $authorize = nel_authorize();
    $authorize->save_users();
    $authorize->save_roles();
    $authorize->save_user_roles();

    if ($redirect)
    {
        if (is_null($redirect_board))
        {
            echo '<meta http-equiv="refresh" content="' . $redirect_delay . ';URL=' . nel_parameters_and_data()->siteSettings('home_page') . '">';
        }
        else
        {
            echo '<meta http-equiv="refresh" content="' . $redirect_delay . ';URL=' .
                 nel_parameters_and_data()->boardReferences($redirect_board, 'board_directory') . '/' . PHP_SELF2 . PHP_EXT . '">';
        }
    }

    die();
}

function get_millisecond_time()
{
    return round(microtime(true), 3) * 1000;
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

function nel_cast_to_datatype($value, $datatype)
{
    if ($datatype === 'bool' || $datatype === 'boolean')
    {
        return (bool) $value;
    }
    else if ($datatype === 'int' || $datatype === 'integer')
    {
        return intval($value);
    }
    else if ($datatype === 'str' || $datatype === 'string')
    {
        return print_r($value, true);
    }
    else
    {
        return $value;
    }
}