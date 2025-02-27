<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Admin\AdminBoardDefaults;
use Nelliel\Account\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;

class DispatchBoardDefaults extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $board_defaults = new AdminBoardDefaults($this->authorization, $this->domain, $this->session);

        switch ($inputs['section']) {
            case 'update':
                if ($inputs['method'] === 'POST') {
                    $board_defaults->update();
                }

                break;

            default:
                if ($inputs['method'] === 'GET') {
                    $board_defaults->panel();
                }
        }
    }
}