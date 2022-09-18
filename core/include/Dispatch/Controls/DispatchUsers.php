<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminUsers;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchUsers extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        $users = new AdminUsers($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $users->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $users->add();
                }

                break;

            case 'modify':
                if ($inputs['method'] === 'GET') {
                    $users->editor($inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $users->update($inputs['id']);
                }

                break;

            case 'delete':
                $users->delete($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $users->panel();
                }
        }
    }
}