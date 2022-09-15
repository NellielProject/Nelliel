<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelBoard;
use Nelliel\Output\OutputPanelMain;

class DispatchMainPanel extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        switch ($inputs['module']) {
            case 'site-main-panel':
                $site_main_panel = new OutputPanelMain($this->domain, false);
                $site_main_panel->render([], false);
                break;

            case 'board-main-panel':
                $board_main_panel = new OutputPanelBoard($this->domain, false);
                $board_main_panel->render([], false);
                break;
        }
    }
}