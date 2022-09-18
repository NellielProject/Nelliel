<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminTemplates;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchTemplates extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        $templates = new AdminTemplates($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'install':
                $templates->install($inputs['id']);
                break;

            case 'uninstall':
                $templates->uninstall($inputs['id']);
                break;

            case 'enable':
                $templates->enable($inputs['id']);
                break;

            case 'disable':
                $templates->disable($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $templates->panel();
                }
        }
    }
}