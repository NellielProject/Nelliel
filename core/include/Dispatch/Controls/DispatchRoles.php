<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminRoles;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchRoles extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $roles = new AdminRoles($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $roles->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $roles->add();
                }

                break;

            case 'modify':
                if ($inputs['method'] === 'GET') {
                    $roles->editor($inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $roles->update($inputs['id']);
                }

                break;

            case 'delete':
                $roles->delete($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $roles->panel();
                }
        }
    }
}