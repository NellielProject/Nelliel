<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Sessions
{
    private static $initialized = false;
    private static $user;
    private $authorize;

    function __construct($authorize)
    {
        $this->authorize = $authorize;

        if (!self::$initialized)
        {
            ini_set('session.use_cookies', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            //ini_set('session.cookie_secure', 1); // TODO: Use this once https properly supported
            self::$initialized = true;
        }
    }

    public function regen()
    {
        if (hash_equals(session_id(), $_COOKIE['PHPSESSID']) && !$this->isOld())
        {
            session_regenerate_id(true);
        }
        else
        {
            $this->terminate();
            nel_derp(220, _gettext('The session id provided is invalid.'));
        }

        $this->setCookie();
    }

    public function initializeSession($module, $board_id = '')
    {
        if (session_status() !== PHP_SESSION_ACTIVE)
        {
            session_start();
        }

        if (!empty($_SESSION))
        {
            if ($this->isOld())
            {
                $this->terminate();
                nel_derp(221, _gettext('This session has expired. Please login again.'));
            }
            else if ($module === 'logout')
            {
                $this->terminate();
                nel_clean_exit(true);
            }
            else
            {
                self::$user = $this->authorize->getUser($_SESSION['username']);
                $this->setVariables();

                if ($module === 'login')
                {
                    if ($board_id === '')
                    {
                        nel_render_main_panel();
                    }
                    else
                    {
                        nel_render_main_board_panel($board_id);
                    }
                }
            }
        }
        else
        {
            if ($module === 'login')
            {
                $this->login($board_id);
            }
            else
            {
                $this->terminate();
            }
        }
    }

    public function terminate()
    {
        session_unset();
        session_destroy();
        setrawcookie("PHPSESSID", "", time() - 7200, "/");
    }

    private function login($board_id)
    {
        if(empty($_POST))
        {
            nel_render_login_page();
            nel_clean_exit();
        }

        if (nel_verify_login())
        {
            $this->setVariables();
            self::$user = $this->authorize->getUser($_SESSION['username']);
        }
        else
        {
            $this->terminate();
            nel_derp(222, _gettext('Login has not been validated or was incorrectly flagged. Cannot start session.'));
        }

        $this->setCookie();

        if ($board_id === '')
        {
            nel_render_main_panel();
        }
        else
        {
            nel_render_main_board_panel($board_id);
        }

        nel_clean_exit();
    }

    private function setVariables()
    {
        if (!isset($_SESSION['username']))
        {
            $_SESSION['username'] = $_POST['username'];
        }

        $_SESSION['ignores'] = array('default' => false);
        $_SESSION['active'] = true;

        if (!isset($_SESSION['login_time']))
        {
            $_SESSION['login_time'] = time();
        }

        $_SESSION['last_activity'] = time();
        $_SESSION['modmode'] = (isset($_GET['modmode'])) ? (bool) $_GET['modmode'] : false;
    }

    private function setCookie()
    {
        setrawcookie(session_name(), session_id(), 0, '/', '; HttpOnly');
    }

    private function isOld()
    {
        return !isset($_SESSION['login_time']) || (time() - $_SESSION['last_activity']) > 3600;
    }

    public function sessionUser()
    {
        return self::$user;
    }

    public function isActive()
    {
        return isset($_SESSION['active']) && $_SESSION['active'];
    }

    public function inModmode($board_id = '')
    {
        if(!$this->isActive())
        {
            return false;
        }

        $board_id = (is_null($board_id)) ? '' : $board_id;
        return isset($_SESSION['modmode']) && $_SESSION['modmode'] && self::$user->boardPerm($board_id, 'perm_modmode_access');
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