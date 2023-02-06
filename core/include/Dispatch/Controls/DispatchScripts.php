<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminScripts;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchScripts extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $scripts = new AdminScripts($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $scripts->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $scripts->add();
                }

                break;

            case 'modify':
                if ($inputs['method'] === 'GET') {
                    $scripts->editor($inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $scripts->update($inputs['id']);
                }

                break;

            case 'delete':
                $scripts->delete($inputs['id']);
                break;

            case 'enable':
                $scripts->enable($inputs['id']);
                break;

            case 'disable':
                $scripts->disable($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $scripts->panel();
                }
        }
    }
}