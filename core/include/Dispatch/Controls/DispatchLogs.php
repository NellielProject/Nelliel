<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelLogs;

class DispatchLogs extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $go_to_panel = true;
        $page = intval(($inputs['page'] ?? 1));
        $log_set = strval($inputs['log_set'] ?? 'combined');

        switch ($inputs['section']) {
            default:
                ;
        }

        if ($go_to_panel) {
            if ($log_set === 'public') {
                $this->verifyPermissions($this->domain, 'perm_view_public_logs');
            } else {
                $this->verifyPermissions($this->domain, 'perm_view_system_logs');
            }

            $output_panel = new OutputPanelLogs($this->domain, false);
            $output_panel->render(['page' => $page, 'log_set' => $log_set], false);
        }
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session->user()->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_view_public_logs':
                nel_derp(355, _gettext('You are not allowed to view the public logs.'), 403);
                break;

            case 'perm_view_system_logs':
                nel_derp(356, _gettext('You are not allowed to view the system logs.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}