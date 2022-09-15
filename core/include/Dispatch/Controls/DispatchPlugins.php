<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminPlugins;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchPlugins extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        $plugins = new AdminPlugins($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'install':
                $plugins->install($inputs['id']);
                break;

            case 'uninstall':
                $plugins->uninstall($inputs['id']);
                break;

            case 'enable':
                $plugins->enable($inputs['id']);
                break;

            case 'disable':
                $plugins->disable($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $plugins->panel();
                }
        }
    }
}