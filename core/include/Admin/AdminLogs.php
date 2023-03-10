<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelLogs;

class AdminLogs extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_SYSTEM_LOGS_TABLE;
        $this->id_column = 'log_id';
        $this->panel_name = _gettext('Logs');
    }

    public function panel(string $log_set, int $page): void
    {
        if ($log_set === 'public') {
            $this->verifyPermissions($this->domain, 'perm_view_public_logs');
        } else {
            $this->verifyPermissions($this->domain, 'perm_view_system_logs');
        }

        $output_panel = new OutputPanelLogs($this->domain, false);
        $output_panel->render(['page' => $page, 'log_set' => $log_set], false);
    }

    public function creator(): void
    {}

    public function add(): void
    {}

    public function editor(): void
    {}

    public function update(): void
    {}

    public function remove(): void
    {}

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_view_public_logs':
                nel_derp(355, _gettext('You are not allowed to view the public logs.'));
                break;

            case 'perm_view_system_logs':
                nel_derp(356, _gettext('You are not allowed to view the system logs.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
