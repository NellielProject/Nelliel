<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class AdminHandler
{
    protected $database;
    protected $authorization;
    protected $domain;
    protected $session_user;
    protected $output_main = true;

    public abstract function actionDispatch(string $action, bool $return);

    public abstract function renderPanel();

    public abstract function creator();

    public abstract function add();

    public abstract function editor();

    public abstract function update();

    public abstract function remove();

    public function outputMain()
    {
        return $this->output_main;
    }

    public function validateUser()
    {
        $session = new \Nelliel\Account\Session();
        $session->loggedInOrError();
        $this->session_user = $session->sessionUser();
    }
}

