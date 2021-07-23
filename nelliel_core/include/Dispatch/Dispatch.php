<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class Dispatch
{
    protected $authorization;
    protected $domain;
    protected $session;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->session = $session;
    }

    public abstract function dispatch(array $inputs);
}