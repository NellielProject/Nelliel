<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

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
        switch ($inputs['section']) {
            case 'captcha':
                $captcha = new CAPTCHA($this->domain);

                if ($inputs['method'] === 'GET') {
                    switch ($inputs['action']) {
                        case 'get':
                            $captcha->get();
                            break;

                        case 'regenerate':
                            $captcha->generate(false);
                            break;
                    }

                    break;
                }
        }
    }
}