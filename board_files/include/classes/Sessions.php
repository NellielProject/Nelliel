<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Sessions
{

    function __construct()
    {
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        //ini_set('session.cookie_secure', 1); // TODO: Use this once https properly supported
    }

    public function regenSession()
    {
        if (hash_equals(session_id(), $_COOKIE['PHPSESSID']) && !$this->sessionIsOld())
        {
            session_regenerate_id(true);
        }
        else
        {
            $this->terminateSession();
            nel_derp(220, _gettext('The session id provided is invalid.'));
        }

        $this->setSessionCookie();
    }

    public function initializeSession($manage, $action, $login_valid = false)
    {
        session_start();

        if (!empty($_SESSION) && !$this->sessionIsOld())
        {
            $_SESSION['last_activity'] = time();
            $_SESSION['ignores'] = array('default' => false);
        }
        else if (!empty($_SESSION) && $this->sessionIsOld())
        {
            $this->terminateSession();
            nel_derp(221, _gettext('This session has expired. Please login again.'));
        }
        else
        {
            if ($manage === 'login' && !is_null($action))
            {
                if ($login_valid)
                {
                    $_SESSION['ignores'] = array('default' => false);
                    $_SESSION['active'] = true;
                    $_SESSION['username'] = $_POST['username'];
                    $_SESSION['login_time'] = time();
                    $_SESSION['last_activity'] = time();
                }
                else
                {
                    $this->terminateSession();
                    nel_derp(222, _gettext('Login has not been validated or was incorrectly flagged. Cannot start session.'));
                }

                $this->setSessionCookie();
                nel_login();
            }
            else if($manage === 'modmode')
            {
                ;
            }
            else
            {
                $this->terminateSession();
                //nel_login();
            }
        }
    }

    public function terminateSession()
    {
        session_unset();
        session_destroy();
        setrawcookie("PHPSESSID", "", time() - 7200, "/");
    }

    private function setSessionCookie()
    {
        setrawcookie(session_name(), session_id(), 0, '/', '; HttpOnly');
    }

    private function sessionIsOld()
    {
        return !isset($_SESSION['login_time']) || (time() - $_SESSION['last_activity']) > 3600;
    }

    public function sessionIsActive()
    {
        return !empty($_SESSION) && $_SESSION['active'];
    }

    public function sessionIsIgnored($reason = 'default', $value = null)
    {
        if (!$this->sessionIsActive())
        {
            return true;
        }

        if (!is_null($value))
        {
            $_SESSION['ignores'][$reason] = $value;
        }

        return isset($_SESSION['ignores'][$reason]) && $_SESSION['ignores'][$reason];
    }
}