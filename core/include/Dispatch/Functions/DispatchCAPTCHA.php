<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Functions;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\AntiSpam\CAPTCHA;
use Nelliel\Account\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchCAPTCHA extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs): void
    {
        $captcha = new CAPTCHA($this->domain);

        switch ($inputs['section']) {
            case 'get':
                $captcha->get();
                break;

            case 'regenerate':
                $captcha->generate(false);
                break;
        }
    }
}