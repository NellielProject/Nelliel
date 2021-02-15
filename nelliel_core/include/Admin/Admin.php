<?php

declare(strict_types=1);

namespace Nelliel\Admin;

use Nelliel\Account\Session;
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
    protected $session;
    protected $session_user;
    protected $output_main = true;
    protected $inputs;

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->session = $session;
        $this->session->loggedInOrError();
        $this->session_user = $session->user();
    }

    public abstract function renderPanel();

    public abstract function creator();

    public abstract function add();

    public abstract function editor();

    public abstract function update();

    public abstract function remove();

    public abstract function enable();

    public abstract function disable();

    public abstract function makeDefault();

    public abstract function verifyAccess();

    public abstract function verifyAction();

    public function outputMain(bool $value = null)
    {
        if (!is_null($value))
        {
            $this->output_main = $value;
        }

        return $this->output_main;
    }
}

