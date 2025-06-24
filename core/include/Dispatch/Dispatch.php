<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Domains\Domain;

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

    public abstract function dispatch(array $inputs): void;

    protected function defaultPermissionError()
    {
        nel_derp(300, __('You do not have permission for that.'), 403);
    }
}