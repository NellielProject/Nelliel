<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Account\Session;
use Nelliel\AntiSpam\CAPTCHA;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class DispatchAntiSpam extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
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