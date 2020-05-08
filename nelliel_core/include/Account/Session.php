<?php

namespace Nelliel\Account;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class Session
{
    protected $domain;
    protected static $initialized = false;
    protected static $session_active = false;
    protected static $in_modmode = false;
    protected static $user;
    protected $session_name = 'NellielSession';
    protected $authorization;
    protected $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->authorization = new Authorization(nel_database());
        $this->database = $domain->database();

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

        if (!self::$session_active)
        {
            $this->setup();
        }
    }

    protected function startSession()
    {
        if (session_status() === PHP_SESSION_ACTIVE)
        {
            return;
        }

        session_name($this->session_name);
        session_start();
    }

    protected function setup()
    {
        if (SECURE_SESSION_ONLY && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off'))
        {
            $this->terminate();
            nel_derp(220, _gettext('Session requires a secure connection.'));
        }

        $empty_session = empty($_SESSION);

        if (!$empty_session && $this->isOld())
        {
            $this->terminate();
            nel_derp(221, _gettext('Session has expired.'));
        }

        if($empty_session)
        {
            return;
        }

        $user = $this->authorization->getUser($_SESSION['user_id']);

        if (!$user || !$user->active())
        {
            nel_derp(222, _gettext('Not an active user.'));
        }

        self::$user = $this->authorization->getUser($_SESSION['user_id']);
        $_SESSION['ignores'] = ['default' => false];
        $_SESSION['last_activity'] = time();
        self::$in_modmode = (isset($_GET['modmode'])) ? (bool) $_GET['modmode'] : false;
        self::$session_active = true;
    }

    public function logout()
    {
        $this->terminate();
        nel_clean_exit(true);
    }

    public function login()
    {
        $login = new \Nelliel\Account\Login($this->authorization, $this->domain);
        $login_data = $login->validate();

        if(empty($login_data))
        {
            $this->terminate();
            nel_derp(223, _gettext('Login has not been validated. Cannot start session.'));
        }

        $_SESSION['user_id'] = $login_data['user_id'];
        self::$user = $this->authorization->getUser($login_data['user_id']);
        $_SESSION['login_time'] = $login_data['login_time'];
        $_SESSION['last_activity'] = $login_data['login_time'];
        self::$session_active = true;
        session_regenerate_id();
        $this->setCookie();
    }

    public function terminate()
    {
        session_unset();
        session_destroy();
        self::$session_active = false;
        $this->setCookie(time() - 7200);
    }

    protected function setCookie($expiry = 0)
    {
        setrawcookie(session_name(), session_id(), 0, '/', '', SECURE_SESSION_ONLY, true);
    }

    public function isOld()
    {
        return (time() - $_SESSION['last_activity']) > 7200;
    }

    public function sessionUser()
    {
        return self::$user;
    }

    public function isActive()
    {
        return self::$session_active;
    }

    public function inModmode(Domain $domain)
    {
        if (!$this->isActive())
        {
            return false;
        }

        return self::$in_modmode && self::$user->checkPermission($domain, 'perm_mod_mode');
    }

    public function loggedInOrError()
    {
        if(is_null(self::$user))
        {
            nel_derp(224, _gettext('You must be logged in for this action.'));
        }
    }
}