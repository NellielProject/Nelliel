<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class AdminLogs extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_LOGS_TABLE;
        $this->id_field = 'log-id';
        $this->id_column = 'log_id';
        $this->panel_name = _gettext('Logs');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_logs_view');
        $output_panel = new \Nelliel\Output\OutputPanelLogs($this->domain, false);
        $log_type = $_GET['log-type'] ?? '';
        $output_panel->render(['log_type' => $log_type], false);
    }

    public function creator(): void
    {
    }

    public function add(): void
    {
    }

    public function editor(): void
    {
    }

    public function update(): void
    {
    }

    public function remove(): void
    {
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm))
        {
            return;
        }

        switch ($perm)
        {
            case 'perm_logs_view':
                nel_derp(355, _gettext('You are not allowed to view the logs.'));
                break;

            case 'perm_logs_manage':
                nel_derp(356, _gettext('You are not allowed to manage the logs.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
