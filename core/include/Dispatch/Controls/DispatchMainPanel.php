<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelMain;

class DispatchMainPanel extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $main_panel = new OutputPanelMain($this->domain, false);

        switch ($inputs['domain_id']) {
            case Domain::SITE:

                $main_panel->site([], false);
                break;

            case Domain::GLOBAL:
                $main_panel->global([], false);
                break;

            default:
                $main_panel->board([], false);
                break;
        }
    }
}