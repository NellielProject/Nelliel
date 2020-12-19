<?php

namespace Nelliel\Account;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\DomainSite;
use Nelliel\LogEvent;
use Nelliel\Auth\Authorization;

class Session
{
    protected $domain;
    protected static $initialized = false;
    protected static $setup_done = false;
    protected static $user;
    protected static $modmode_requested = false;
    protected $session_name = 'NellielSession';
    protected $authorization;
    protected $database;
    protected $failed = false;
    protected static $ignore = false;

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
    }

    protected function started()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    protected function init(bool $do_setup)
    {
        if (!$this->started())
        {
            session_name($this->session_name);
            session_start();
        }

        if (!self::$setup_done && $do_setup)
        {
            $this->setup();
        }
    }

    protected function setup()
    {
        if (self::$setup_done)
        {
            return;
        }

        if (NEL_SECURE_SESSION_ONLY && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off'))
        {
            $this->terminate();
            $this->failed = true;
            nel_derp(220, _gettext('Session requires a secure connection.'));
        }

        if (!empty($_SESSION))
        {
            if ($this->isOld())
            {
                $this->terminate();
                $this->failed = true;
                nel_derp(221, _gettext('Session has expired.'));
            }
        }
        else
        {
            return;
        }

        $user = $this->authorization->getUser($_SESSION['user_id'] ?? '');

        if ($user->empty() || !$user->active())
        {
            $this->failed = true;
            $this->terminate();
            nel_derp(222, _gettext('User does not exist or is inactive.'));
        }

        self::$user = $user;
        $_SESSION['ignores'] = ['default' => false];
        $_SESSION['last_activity'] = time();
        self::$modmode_requested = (isset($_GET['modmode']) && $_GET['modmode'] === 'true') ||
                isset($_POST['in_modmode']) && $_POST['in_modmode'] === 'true';
        self::$setup_done = true;
    }

    public function logout()
    {
        $this->init(true);
        $this->terminate();

        if(!empty(self::$user))
        {
            $log_event = new LogEvent($this->domain);
            $log_event->changeContext('event_id', 'LOGOUT_SUCCESS');
            $log_event->send(sprintf(_gettext("User %s logged out."), self::$user->id()));
        }

        $output_login = new \Nelliel\Output\OutputLoginPage($this->domain, false);
        $output_login->render([], false);
        nel_clean_exit(false);
    }

    public function login()
    {
        $this->doing_login = true;
        $this->init(true);
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
        $log_event = new LogEvent($this->domain);
        $log_event->changeContext('event_id', 'LOGIN_SUCCESS');
        $log_event->send(sprintf(_gettext("User %s logged in."), self::$user->id()));
        $_SESSION['login_time'] = $login_data['login_time'];
        $_SESSION['last_activity'] = $login_data['login_time'];
        session_regenerate_id();
        $this->setCookie();
        $this->doing_login = false;
    }

    public function terminate()
    {
        session_unset();

        if ($this->started())
        {
            session_destroy();
        }

        self::$setup_done = false;
        $this->setCookie(time() - 7200);
    }

    public function ignore(bool $ignore = null)
    {
        if (!is_null($ignore))
        {
            self::$ignore = $ignore;
        }

        return self::$ignore;
    }

    protected function setCookie($expiry = 0)
    {
        setrawcookie(session_name(), session_id(), 0, '/', '', NEL_SECURE_SESSION_ONLY, true);
    }

    protected function isOld()
    {
        if ($this->domain->setting('session_length') == 0)
        {
            return false;
        }

        $last_activity = $_SESSION['last_activity'] ?? 0;
        return (time() - $last_activity) > $this->domain->setting('session_length');
    }

    public function sessionUser()
    {
        $this->init(true);
        return self::$user;
    }

    public function isActive()
    {
        if(!$this->ignore())
        {
            $this->init(true);
        }

        return self::$setup_done;
    }

    public function inModmode(Domain $domain)
    {
        return $this->isActive() && self::$modmode_requested && self::$user->checkPermission($domain, 'perm_board_mod_mode');
    }

    public function loggedInOrError()
    {
        if (!$this->isActive() || empty(self::$user))
        {
            $this->failed = true;
            nel_derp(224, _gettext('You must be logged in for this action.'));
        }
    }
}