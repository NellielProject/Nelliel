<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_is_in_string($string, $substring)
{
    if(strripos($string, $substring) !== false)
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
    //$dataforce['post_links'] = nel_cache_links($dataforce['post_links']);
    //nel_write_multi_cache($dataforce);

    $authorize = nel_get_authorization();
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
