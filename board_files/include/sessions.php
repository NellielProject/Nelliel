<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// End and delete session
//
function nel_terminate_session()
{
    session_unset();
    session_destroy();
    setcookie("PHPSESSID", "", time() - 3600, "/");
}

//
// Regenerate session (Swiper no swiping!)
//
function nel_regen_session()
{
    $timeout = time() - $_SESSION['last_activity'];

    if ($_COOKIE['PHPSESSID'] === session_id() && $timeout < 1800)
    {
        session_regenerate_id(true);
        $_SESSION['last_activity'] = time();
        $_SESSION['ignore_login'] = FALSE;
        $_SESSION['ignores'] = array();
    }
    else // Session timed out or doesn't match the cookie
    {
        nel_terminate_session();
        nel_derp(105, array('origin' => 'SESSION_REGEN'));
    }

    nel_set_session_cookie();
}

function nel_set_session_cookie()
{
    setcookie(session_name(), session_id(), 0, '/', '; HttpOnly');
}

//
// Check for existing session and process
// If no session exists, confirm login info and set up a new one
//
function nel_initialize_session($dataforce)
{
    $authorize = nel_get_authorization();
    session_start();
    require_once INCLUDE_PATH . 'output/login_page.php';
    require_once INCLUDE_PATH . 'admin/login.php';

    if (!empty($_SESSION))
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
                nel_regen_session();
                nel_login($dataforce, $authorize);
                die();
            }
        }
        else if (isset($dataforce['admin_mode']))
        {
            nel_regen_session();
        }
        else
        {
        }
    }
    else if (isset($dataforce['mode']) && $dataforce['mode'] === 'admin->login') // No existing session but this may be a login attempt
    {
        if ($dataforce['username'] !== '' && nel_password_verify($dataforce['admin_pass'], $authorize->get_user_info($dataforce['username'], 'user_password')))
        {
            // We set up the session here
            $_SESSION['ignore_login'] = FALSE;
            $_SESSION['ignores'] = array();
            $_SESSION['active'] = true;
            $_SESSION['username'] = $dataforce['username'];
            $_SESSION['role_id'] = $authorize->get_user_role($dataforce['username']);
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
    else
    {
        nel_terminate_session();
    }
}

function nel_session_active()
{
    static $status;

    if(!empty($_SESSION))
    {
        $status = $_SESSION['active'];
    }
    else
    {
        $status = false;
    }

    return $status;
}

function nel_session_ignored()
{
    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

function nel_session_is_ignored($reason)
{
    if(!nel_session_active())
    {
        return true;
    }

    return isset($_SESSION['ignores'][$reason]) && $_SESSION['ignores'][$reason];
}

function nel_session_set_ignored($reason, $value)
{
    if(nel_session_active())
    {
        $_SESSION['ignores'][$reason] = $value;
    }
}
