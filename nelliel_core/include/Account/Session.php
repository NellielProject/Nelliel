<?php

namespace Nelliel\Account;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\DomainSite;
use Nelliel\Auth\Authorization;

class Session
{
    protected $domain;
    protected static $initialized = false;
    protected static $session_active = false;
    protected static $user;
    protected static $modmode_requested = false;
    protected $session_name = 'NellielSession';
    protected $authorization;
    protected $database;
    protected $failed = false;

    function __construct()
    {
        if ($this->failed)
        {
            return;
        }

        $this->domain = new DomainSite(nel_database());
        $this->database = $this->domain->database();
        $this->authorization = new Authorization($this->database);

        if (!self::$initialized)
        {
            ini_set('session.use_cookies', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);

            if (NEL_SECURE_SESSION_ONLY)
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

    protected function valid()
    {
        if (!self::$session_active)
        {
            $this->setup();
        }

        return true;
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
        if (NEL_SECURE_SESSION_ONLY && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off'))
        {
            $this->terminate();
            $this->failed = true;
            nel_derp(220, _gettext('Session requires a secure connection.'));
        }

        $empty_session = empty($_SESSION);

        if (!$empty_session && $this->isOld())
        {
            $this->terminate();
            $this->failed = true;
            nel_derp(221, _gettext('Session has expired.'));
        }

        if ($empty_session)
        {
            return;
        }

        $user = $this->authorization->getUser($_SESSION['user_id']);

        if ($user->empty() || !$user->active())
        {
            $this->failed = true;
            nel_derp(222, _gettext('Not an active user.'));
        }

        self::$user = $this->authorization->getUser($_SESSION['user_id']);
        $_SESSION['ignores'] = ['default' => false];
        $_SESSION['last_activity'] = time();
        $self::$modmode_requested = (isset($_GET['modmode']) && $_GET['modmode'] === 'true') ||
                isset($_POST['in_modmode']) && $_POST['in_modmode'] === 'true';
        self::$session_active = true;
    }

    public function logout()
    {
        $this->valid();
        $this->terminate();
        $output_login = new \Nelliel\Output\OutputLoginPage($this->domain, false);
        $output_login->render(['dotdot' => ''], false);
        nel_clean_exit(false);
    }

    public function login()
    {
        $this->valid();
        $login = new \Nelliel\Account\Login($this->authorization, $this->domain);
        $login_data = $login->validate();

        if (empty($login_data))
        {
            $this->terminate();
            $this->failed = true;
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
        setrawcookie(session_name(), session_id(), 0, '/', '', NEL_SECURE_SESSION_ONLY, true);
    }

    public function isOld()
    {
        $this->valid();

        if ($this->domain->setting('session_length') == 0)
        {
            return false;
        }

        return (time() - $_SESSION['last_activity']) > $this->domain->setting('session_length');
    }

    public function sessionUser()
    {
        $this->valid();
        return self::$user;
    }

    public function isActive()
    {
        $this->valid();
        return self::$session_active;
    }

    public function inModmode(Domain $domain)
    {
        $this->valid();
        return $this->isActive() && self::$modmode_requested && self::$user->checkPermission($domain, 'perm_mod_mode');
    }

    public function loggedInOrError()
    {
        $this->valid();

        if (is_null(self::$user))
        {
            $this->failed = true;
            nel_derp(224, _gettext('You must be logged in for this action.'));
        }
    }
}