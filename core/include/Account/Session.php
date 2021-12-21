<?php
declare(strict_types = 1);

namespace Nelliel\Account;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\LogEvent;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputLoginPage;

class Session
{
    protected static $setup_done = false;
    protected static $user;
    protected static $modmode = false;
    protected static $ignore = false;
    protected $session_name = 'NellielSession';
    protected $authorization;
    protected $domain;
    protected $doing_login = false;
    protected $session_options = array();


    function __construct()
    {
        $this->session_options['use_strict_mode'] = true;
        $this->session_options['use_cookies'] = true;
        $this->session_options['use_only_cookies'] = true;
        $this->session_options['cookie_httponly'] = true;
        $this->session_options['cookie_lifetime'] = 0;
        $this->session_options['cookie_path'] = NEL_BASE_WEB_PATH;

        if (NEL_SECURE_SESSION_ONLY) {
            $this->session_options['cookie_secure'] = true;
        }

        if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
            $this->session_options['cookie_samesite'] = 'Strict';
        } else {
            $this->session_options['cookie_path'] = NEL_BASE_WEB_PATH . '; samesite=strict';
        }

        $this->domain = nel_site_domain();
        $this->authorization = new Authorization(nel_database('core'));

        if (empty(self::$user)) {
            self::$user = $this->authorization->emptyUser();
        }
    }

    protected function started()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function init(bool $do_setup)
    {
        if (!$this->started()) {
            session_name($this->session_name);
            session_start($this->session_options);
        }

        if (!self::$setup_done && $do_setup) {
            $this->setup();
        }
    }

    protected function setup()
    {
        if (self::$setup_done) {
            return;
        }

        if (NEL_SECURE_SESSION_ONLY && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off')) {
            $this->terminate();
            nel_derp(220, _gettext('Session requires a secure connection.'));
        }

        if (!empty($_SESSION)) {
            if ($this->isOld() && !$this->doing_login) {
                $this->terminate();
                nel_derp(221, _gettext('Session has expired.'));
            }
        } else {
            if (!$this->doing_login) {
                return;
            }
        }

        if (!$this->doing_login) {
            $user = $this->authorization->getUser($_SESSION['user_id'] ?? '');

            if ($user->empty() || !$user->active()) {
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

        if (!empty(self::$user)) {
            $log_event = new LogEvent(nel_site_domain());
            $log_event->changeContext('event', 'LOGOUT_SUCCESS');
            $log_event->send(sprintf(_gettext("User %s logged out."), self::$user->id()));
        }

        $this->terminate();
        $output_login = new OutputLoginPage($this->domain, false);
        $output_login->render([], false);
        nel_clean_exit(false);
    }

    public function login()
    {
        $this->doing_login = true;
        $this->init(true);
        $login = new Login($this->authorization, $this->domain);
        self::$user = $login->validate();
        $_SESSION['user_id'] = self::$user->id();
        $log_event = new LogEvent(nel_site_domain());
        $log_event->changeContext('event', 'LOGIN_SUCCESS');
        $log_event->send(sprintf(_gettext("User %s logged in."), self::$user->id()));
        $_SESSION['login_time'] = self::$user->getData('last_login');
        $_SESSION['last_activity'] = self::$user->getData('last_login');
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
        if (!is_null($ignore)) {
            self::$ignore = $ignore;
        }

        return self::$ignore;
    }

    protected function isOld()
    {
        if ($this->domain->setting('session_length') == 0) {
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
        return !self::$ignore && self::$setup_done;
    }

    public function toggleModMode(): void
    {
        self::$modmode = !self::$modmode;
    }

    public function inModmode(Domain $domain)
    {
        return $this->isActive() && self::$modmode && self::$user->checkPermission($domain, 'perm_mod_mode');
    }

    public function loggedInOrError()
    {
        if (!$this->isActive() || (!$this->doing_login && empty(self::$user))) {
            nel_derp(224, _gettext('You must be logged in for this action.'));
        }
    }
}