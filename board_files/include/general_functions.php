<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_clean_exit($redirect = false, $redirect_board = null, $redirect_delay = 2)
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $authorization->saveUsers();
    $authorization->saveRoles();
    //$authorization->saveUserRoles();

    if ($redirect)
    {
        if (is_null($redirect_board))
        {
            nel_redirect(nel_parameters_and_data()->siteSettings('home_page'), $redirect_delay);
        }
        else
        {
            $url = nel_parameters_and_data()->boardReferences($redirect_board, 'board_directory') . '/' . MAIN_INDEX .
                    PAGE_EXT;
            nel_redirect($url, $redirect_delay);
        }
    }

    die();
}

function nel_redirect($url, $delay, $output = true)
{
    $redirect = '<meta http-equiv="refresh" content="' . $delay . ';URL=' . $url . '">';

    if ($output)
    {
        echo $redirect;
    }
    else
    {
        return $redirect;
    }
}

function nel_get_microtime($convert_int = true)
{
    $time = microtime();
    $return_time = ['time' => $time];
    $split_time = explode(' ', $time);

    if ($convert_int)
    {
        $return_time = ['time' => intval($split_time[1]), 'milli' => intval(round($split_time[0], 3) * 1000),
            'micro' => intval($split_time[0] * 1000000)];
    }
    else
    {
        $return_time = ['time' => (float) $split_time[1], 'milli' => round($split_time[0], 3),
            'micro' => (float) $split_time[0] * 1000000];
    }

    return $return_time;
}

function nel_utf8_to_numeric_html_entities(&$input, $non_ascii_only = true)
{
    $regex = ($non_ascii_only) ? '#([^[:ascii:]])#Su' : '#(.)#Su';

    $input = preg_replace_callback($regex,
            function ($matches)
            {
                return '&#' . utf8_ord($matches[0]) . ';';
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

function nel_cast_to_datatype($value, $datatype)
{
    if(is_null($value))
    {
        return $value;
    }

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

function nel_setup_stuff_done($status = null)
{
    static $stuff_done = false;
    $stuff_done = (is_bool($status)) ? $status : $stuff_done;
    return $stuff_done;
}

function nel_true_empty($variable)
{
    return is_null($variable) || $variable === '' || $variable === array();
}

function nel_get_captcha()
{
    $captcha_instance = new \Nelliel\CAPTCHA(nel_database());
    $captcha_instance->getCaptcha();
    die();
}