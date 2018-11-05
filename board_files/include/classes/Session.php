<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Session
{
    private static $initialized = false;
    private static $session_active = false;
    private static $in_modmode = false;
    private static $user;
    private $authorization;

    function __construct($authorization, $setup = false)
    {
        $this->authorization = $authorization;

        if (!self::$initialized)
        {
            ini_set('session.use_cookies', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            //ini_set('session.cookie_secure', 1); // TODO: Use this once https properly supported
            self::$initialized = true;
        }

        $this->startSession();

        // We need to allow for session to be started but only setup when needed?
        if ($setup && !self::$session_active)
        {
            $this->setup();
        }
    }

    public function startSession()
    {
        if (session_status() === PHP_SESSION_ACTIVE)
        {
            return;
        }

        session_start();
    }

    public function setup()
    {
        $this->startSession();

        if (!$this->setVariables())
        {
            nel_derp(223, _gettext('No valid session or session set up failed.'));
        }

        self::$session_active = true;
    }

    public function logout()
    {
        $this->terminate();
        nel_clean_exit(true);
    }

    public function login()
    {
        if (nel_verify_login())
        {
            $this->startSession();
        }
        else
        {
            $this->terminate();
            nel_derp(222, _gettext('Login has not been validated or was incorrectly flagged. Cannot start session.'));
        }

        $this->setCookie();
    }

    public function terminate()
    {
        session_unset();

        if (session_status() === PHP_SESSION_ACTIVE)
        {
            session_destroy();
        }

        self::$session_active = false;
        setrawcookie("PHPSESSID", "", time() - 7200, "/");
    }

    private function setVariables()
    {
        if (!isset($_SESSION['username']))
        {
            if (!isset($_POST['username']))
            {
                return false;
            }

            $_SESSION['username'] = $_POST['username'];
        }

        self::$user = $this->authorization->getUser($_SESSION['username']);
        $_SESSION['ignores'] = array('default' => false);

        if (!isset($_SESSION['login_time']))
        {
            $_SESSION['login_time'] = time();
        }

        $_SESSION['last_activity'] = time();
        self::$in_modmode = (isset($_GET['modmode'])) ? (bool) $_GET['modmode'] : false;
        return true;
    }

    private function setCookie()
    {
        setrawcookie(session_name(), session_id(), 0, '/', '; HttpOnly');
    }

    public function isOld()
    {
        return !isset($_SESSION['login_time']) || (time() - $_SESSION['last_activity']) > 3600;
    }

    public function sessionUser()
    {
        $this->startSession();
        return self::$user;
    }

    public function isActive()
    {
        return self::$session_active;
    }

    public function inModmode($board_id = '')
    {
        if (!$this->isActive())
        {
            return false;
        }

        $board_id = (is_null($board_id)) ? '' : $board_id;
        return self::$in_modmode && self::$user->boardPerm($board_id, 'perm_modmode_access');
    }

    public function isIgnored($reason = 'default', $value = null)
    {
        if (!$this->isActive())
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