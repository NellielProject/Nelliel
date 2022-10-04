<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminPermissions;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchPermissions extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $permissions = new AdminPermissions($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $permissions->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $permissions->add();
                }

                break;

            case 'delete':
                $permissions->delete($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $permissions->panel();
                }
        }
    }
}