<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminStyles;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchStyles extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        $styles = new AdminStyles($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'install':
                $styles->install($inputs['id']);
                break;

            case 'uninstall':
                $styles->uninstall($inputs['id']);
                break;

            case 'enable':
                $styles->enable($inputs['id']);
                break;

            case 'disable':
                $styles->disable($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $styles->panel();
                }
        }
    }
}