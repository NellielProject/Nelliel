<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Auth\Authorization;

class Session
{
    private static $initialized = false;
    private static $session_active = false;
    private static $in_modmode = false;
    private static $modmode;
    private static $user;
    private $session_name = 'NellielSession';
    private $authorization;

    function __construct(Authorization $authorization, bool $setup = false)
    {
        $this->authorization = $authorization;

        if (!self::$initialized)
        {
            ini_set('session.use_cookies', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);

            if (SECURE_SESSION_ONLY)
            {
                ini_set('session.cookie_secure', 1);
            }

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

        session_name($this->session_name);
        session_start();
    }

    public function setup(bool $login = false)
    {
        if (SECURE_SESSION_ONLY && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off'))
        {
            $this->terminate();
            nel_derp(224, _gettext('Session requires a secure connection.'));
        }

        $this->startSession();

        if (!$login)
        {
            if (empty($_SESSION))
            {
                nel_derp(220, _gettext('No valid session found.'));
            }

            if ($this->isOld())
            {
                $this->terminate();
                nel_derp(221, _gettext('Session has expired.'));
            }
        }

        if (!$this->setVariables())
        {
            nel_derp(223, _gettext('Session set up failed.'));
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
            $this->setup(true);
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
        session_destroy();
        self::$session_active = false;
        $this->setCookie(time() - 7200);
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

        $user = $this->authorization->getUser($_SESSION['username']);

        if(!$user->active())
        {
            nel_derp(225, _gettext('Not an active user.'));
        }

        self::$user = $this->authorization->getUser($_SESSION['username']);
        $_SESSION['ignores'] = ['default' => false];

        if (!isset($_SESSION['login_time']))
        {
            $_SESSION['login_time'] = time();
        }

        $_SESSION['last_activity'] = time();
        self::$in_modmode = (isset($_GET['modmode'])) ? (bool) $_GET['modmode'] : false;
        self::$modmode = new ModMode();
        return true;
    }

    private function setCookie($expiry = 0)
    {
        setrawcookie(session_name(), session_id(), 0, '/', '', SECURE_SESSION_ONLY, true);
    }

    public function isOld()
    {
        return (time() - $_SESSION['last_activity']) > 7200;
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

    public function modmode()
    {
        return self::$modmode;
    }

    public function inModmode(Domain $domain)
    {
        if (!$this->isActive())
        {
            return false;
        }

        return self::$in_modmode && self::$user->domainPermission($domain, 'perm_modmode_access');
    }
}