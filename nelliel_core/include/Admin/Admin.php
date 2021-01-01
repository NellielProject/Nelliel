<?php

namespace Nelliel\Admin;

use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class Admin
{
    protected $database;
    protected $authorization;
    protected $domain;
    protected $session_user;
    protected $output_main = true;
    protected $inputs;

    function __construct(Authorization $authorization, Domain $domain, array $inputs)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->validateUser();
    }

    public abstract function renderPanel();

    public abstract function creator();

    public abstract function add();

    public abstract function editor();

    public abstract function update();

    public abstract function remove();

    public function outputMain(bool $value = null)
    {
        if (!is_null($value))
        {
            $this->output_main = $value;
        }

        return $this->output_main;
    }

    public function validateUser()
    {
        $session = new \Nelliel\Account\Session();
        $session->loggedInOrError();
        $this->session_user = $session->sessionUser();
    }
}

