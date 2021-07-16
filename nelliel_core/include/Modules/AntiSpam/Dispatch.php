<?php
declare(strict_types = 1);

namespace Nelliel\Modules\AntiSpam;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Modules\Account\Session;

class Dispatch
{
    private $domain;
    private $session;

    function __construct(Domain $domain, Authorization $authorization, Session $session)
    {
        $this->domain = $domain;
        $this->session = $session;
    }

    public function dispatch(array $inputs)
    {
        switch ($inputs['section'])
        {
            case 'captcha':
                $captcha = new CAPTCHA($this->domain);
                $captcha->dispatch($inputs);
                break;
        }
    }
}