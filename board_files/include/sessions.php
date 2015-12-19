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
}

//
// Check for existing session and process
// If no session exists, confirm login info and set up a new one
//
function nel_initialize_session($dataforce)
{
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
                nel_valid($dataforce);
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
        if ($dataforce['username'] !== '' && nel_hash($dataforce['admin_pass']) === nel_get_user_setting($dataforce['username'], 'staff_password'))
        {
            // We set up the session here
            $_SESSION['ignore_login'] = FALSE;
            $_SESSION['username'] = $dataforce['username'];
            $_SESSION['password'] = $dataforce['admin_pass'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
        }
        else
        {
            nel_terminate_session();
            nel_derp(107, array('origin' => 'SESSION_INIT'));
        }
        
        nel_valid($dataforce);
        die();
    }
    else
    {
        nel_terminate_session();
    }
}
?>