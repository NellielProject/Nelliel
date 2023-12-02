<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminBans;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchBans extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $bans = new AdminBans($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'GET') {
                    $bans->creator();
                }

                if ($inputs['method'] === 'POST') {
                    $bans->add();
                }

                break;

            case 'modify':
                if ($inputs['method'] === 'GET') {
                    $bans->editor($inputs['id']);
                }

                if ($inputs['method'] === 'POST') {
                    $bans->update($inputs['id']);
                }

                break;

            case 'delete':
                $bans->delete($inputs['id']);
                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $page = (int) ($inputs['page'] ?? 1);
                    $bans->panel($page);
                }
        }
    }
}