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
    if(version_compare(PHP_VERSION, '5.2.0', '<'))
    {
        setcookie(session_name(), session_id(), 0, '/', '; HttpOnly');
    }
    else
    {
        setcookie(session_name(), session_id(), 0, '/', '', NULL, TRUE );
    }
}

//
// Check for existing session and process
// If no session exists, confirm login info and set up a new one
//
function nel_initialize_session($dataforce, $plugins, $authorize)
{
    session_start();

    if (!empty($_SESSION))
    {
        if (isset($dataforce['mode2']))
        {
            if ($dataforce['mode2'] === 'log_out')
            {
                nel_terminate_session();
                echo '<meta http-equiv="refresh" content="0;URL=' . PHP_SELF2 . PHP_EXT . '">';
                die();
            }
            else if ($dataforce['mode2'] === 'admin')
            {
                nel_regen_session();
                nel_valid($dataforce, $authorize);
                die();
            }
        }
        else if (isset($dataforce['admin_mode']))
        {
            nel_regen_session();
        }
        else
        {
            $_SESSION['ignore_login'] = TRUE;
        }
    }
    else if (isset($dataforce['admin_mode']) && $dataforce['admin_mode'] === 'login') // No existing session but this may be a login attempt
    {
        if ($dataforce['username'] !== '' && nel_hash($dataforce['admin_pass'], $plugins) === $authorize->get_user_setting($dataforce['username'], 'staff_password'))
        {
            // We set up the session here
            $_SESSION['ignore_login'] = FALSE;
            $_SESSION['username'] = $dataforce['username'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            $user_auth = $authorize->get_user_auth($dataforce['username']);
            $_SESSION['perms'] = $user_auth['perms'];
            $_SESSION['settings'] = $user_auth['settings'];
        }
        else
        {
            nel_terminate_session();
            nel_derp(107, array('origin' => 'SESSION_INIT'));
        }
        
        nel_set_session_cookie();
        nel_valid($dataforce, $authorize);
        die();
    }
    else
    {
        nel_terminate_session();
    }
}
?>