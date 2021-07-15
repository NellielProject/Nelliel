<?php
declare(strict_types = 1);

namespace Nelliel\Modules\Account;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\LogEvent;
use Nelliel\Auth\Authorization;

class Session
{
    protected $domain;
    protected static $setup_done = false;
    protected static $user;
    protected $session_name = 'NellielSession';
    protected $authorization;
    protected $database;
    protected $failed = false;
    protected static $ignore = false;
    protected $doing_login = false;
    protected $session_options = array();

    function __construct()
    {
        if ($this->failed)
        {
            return;
        }

        $this->session_options['use_strict_mode'] = true;
        $this->session_options['use_cookies'] = true;
        $this->session_options['use_only_cookies'] = true;
        $this->session_options['cookie_httponly'] = true;
        $this->session_options['cookie_lifetime'] = 0;
        $this->session_options['cookie_path'] = NEL_BASE_WEB_PATH;

        if (NEL_SECURE_SESSION_ONLY)
        {
            $this->session_options['cookie_secure'] = true;
        }

        if (version_compare(PHP_VERSION, '7.3.0', '>='))
        {
            $this->session_options['cookie_samesite'] = 'Strict';
        }
        else
        {
            $this->session_options['cookie_path'] = NEL_BASE_WEB_PATH . '; samesite=strict';
        }

        $this->domain = nel_site_domain();
        $this->database = $this->domain->database();
        $this->authorization = new Authorization($this->database);

        if (empty(self::$user))
        {
            self::$user = $this->authorization->emptyUser();
        }
    }

    protected function started()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function init(bool $do_setup)
    {
        if (!$this->started())
        {
            session_name($this->session_name);
            session_start($this->session_options);
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
            if (!$this->doing_login)
            {
                return;
            }
        }

        if (!$this->doing_login)
        {
            $user = $this->authorization->getUser($_SESSION['user_id'] ?? '');

            if ($user->empty() || !$user->active())
            {
                $this->failed = true;
                $this->terminate();
                nel_derp(222, _gettext('User does not exist or is inactive.'));
            }

            self::$user = $user;
        }

        $_SESSION['ignores'] = ['default' => false];
        $_SESSION['last_activity'] = time();
        self::$setup_done = true;
    }

    public function logout()
    {
        $this->init(true);

        if (!empty(self::$user))
        {
            $log_event = new LogEvent(nel_site_domain());
            $log_event->changeContext('event_id', 'LOGOUT_SUCCESS');
            $log_event->send(sprintf(_gettext("User %s logged out."), self::$user->id()));
        }

        $this->terminate();
        $output_login = new \Nelliel\Render\OutputLoginPage($this->domain, false);
        $output_login->render([], false);
        nel_clean_exit(false);
    }

    public function login()
    {
        $this->doing_login = true;
        $this->init(true);
        $login = new Login($this->authorization, $this->domain);
        $login_data = $login->validate();

        if (empty($login_data))
        {
            $this->terminate();
            $this->failed = true;
            nel_derp(223, _gettext('Login has not been validated. Cannot start session.'));
        }

        $_SESSION['user_id'] = $login_data['user_id'];
        self::$user = $this->authorization->getUser($login_data['user_id']);
        $log_event = new LogEvent(nel_site_domain());
        $log_event->changeContext('event_id', 'LOGIN_SUCCESS');
        $log_event->send(sprintf(_gettext("User %s logged in."), self::$user->id()));
        $_SESSION['login_time'] = $login_data['login_time'];
        $_SESSION['last_activity'] = $login_data['login_time'];
        session_regenerate_id();
        $this->doing_login = false;
    }

    public function terminate()
    {
        $_SESSION = array();
        setcookie(session_name(), '', time() - NEL_OVER_9000, NEL_BASE_WEB_PATH);
        session_destroy();
        self::$setup_done = false;
        self::$user = null;
        self::$ignore = false;
    }

    public function ignore(bool $ignore = null)
    {
        if (!is_null($ignore))
        {
            self::$ignore = $ignore;
        }

        return self::$ignore;
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

    public function user()
    {
        return self::$user ?? $this->authorization->emptyUser();
    }

    public function isActive()
    {
        return !$this->ignore() && self::$setup_done;
    }

    public function modmodeRequested()
    {
        return (isset($_POST['modmode']) && $_POST['modmode'] === 'true') ||
                (isset($_GET['modmode']) && $_GET['modmode'] === 'true');
    }

    public function inModmode(Domain $domain)
    {
        return $this->isActive() && $this->modmodeRequested() && self::$user->checkPermission($domain, 'perm_mod_mode');
    }

    public function loggedInOrError()
    {
        if (!$this->isActive() || (!$this->doing_login && empty(self::$user)))
        {
            $this->failed = true;
            nel_derp(224, _gettext('You must be logged in for this action.'));
        }
    }
}