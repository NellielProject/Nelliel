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

// TODO: Update this, it probably doesn't even work
function nel_parse_links($matches)
{
    global $link_resno;
    $dbh = nel_database();
    $back = ($link_resno === 0) ? PAGE_DIR : '../';
    $prepared = $dbh->prepare('SELECT response_to FROM ' . POST_TABLE . ' WHERE post_number=?');
    $prepared->bindParam(1, $matches[1], PDO::PARAM_INT);
    $prepared->execute();
    $link = $prepared->fetch(PDO::FETCH_NUM);
    $prepared->closeCursor();

    if ($link === false)
    {
        return $matches[0];
    }

    if ($link[0] == '0')
    {
        return '<a href="' . $back . $matches[1] . '/' . $matches[1] . '.html" class="link_quote">>>' . $matches[1] .
        '</a>';
    }
    else
    {
        return '<a href="' . $back . $link . '/' . $link . '.html#' . $matches[1] . '" class="link_quote">>>' .
        $matches[1] . '</a>';
    }
}