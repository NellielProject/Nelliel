<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminCapcodes;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchCapcodes extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        $capcodes = new AdminCapcodes($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $capcodes->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $capcodes->add();
                }

                break;

            case 'modify':
                if ($inputs['method'] === 'GET') {
                    $capcodes->editor($inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $capcodes->update($inputs['id']);
                }

                break;

            case 'delete':
                $capcodes->delete($inputs['id']);
                break;

            case 'enable':
                $capcodes->enable($inputs['id']);
                break;

            case 'disable':
                $capcodes->disable($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $capcodes->panel();
                }
        }
    }
}