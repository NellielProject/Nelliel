<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelIPInfo;

class DispatchIPInfo extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $id = $inputs['id'] ?? '';

        switch ($inputs['section']) {
            case 'view':
                if (!$this->session_user->checkPermission($this->domain, 'perm_view_ip_info')) {
                    nel_derp(440, _gettext('You are not allowed to view IP info.'));
                }

                $output_panel = new OutputPanelIPInfo($this->domain, false);
                $output_panel->render(['id' => $id], false);

            default:
        }
    }
}