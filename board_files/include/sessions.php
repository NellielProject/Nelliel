<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_session_settings()
{
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    //ini_set('session.cookie_secure', 1); // TODO: Use this once https properly supported
}

//
// Regenerate session (Swiper no swiping!)
//
function nel_regen_session()
{
    if (hash_equals(session_id(), $_COOKIE['PHPSESSID']) && !nel_session_is_old())
    {
        session_regenerate_id(true);
        $_SESSION['last_activity'] = time();
        $_SESSION['ignores'] = array('default' => false);
    }
    else
    {
        nel_terminate_session();
        nel_derp(105, array('origin' => 'SESSION_REGEN'));
    }

    nel_set_session_cookie();
}

//
// Check for existing session and process
// If no session exists, confirm login info and set up a new one
//
function nel_initialize_session($dataforce)
{
    $authorize = nel_authorize();
    session_start();

    if (!empty($_SESSION) && !nel_session_is_old())
    {
        if (isset($dataforce['get_mode']))
        {
            if ($dataforce['get_mode'] === 'log_out')
            {
                nel_terminate_session();
                echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF2 . PHP_EXT . '">';
                die();
            }
            else if ($dataforce['get_mode'] === 'admin')
            {
                nel_login($dataforce);
                die();
            }
        }
    }
    else
    {
        if ($dataforce['login_valid'])
        {
            $_SESSION['ignores'] = array('default' => false);
            $_SESSION['active'] = true;
            $_SESSION['username'] = $dataforce['username'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
        }
        else
        {
            nel_terminate_session();
            nel_derp(107, array('origin' => 'SESSION_INIT'));
        }

        nel_set_session_cookie();
        nel_login($dataforce);
        die();
    }
}

function nel_terminate_session()
{
    session_unset();
    session_destroy();
    setcookie("PHPSESSID", "", time() - 3600, "/");
}

function nel_set_session_cookie()
{
    setcookie(session_name(), session_id(), 0, '/', '; HttpOnly');
}

function nel_session_is_old()
{
    return !isset($_SESSION['login_time']) || (time() - $_SESSION['last_activity']) > 1800;
}

function nel_session_is_active()
{
    return !empty($_SESSION) && $_SESSION['active'];
}

function nel_session_is_ignored($reason = 'default', $value = null)
{
    if (!nel_session_is_active())
    {
        return true;
    }

    if(!is_null($value))
    {
        $_SESSION['ignores'][$reason] = $value;
    }

    return isset($_SESSION['ignores'][$reason]) && $_SESSION['ignores'][$reason];
}
