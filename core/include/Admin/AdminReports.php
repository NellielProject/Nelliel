<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelReports;

class AdminReports extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_REPORTS_TABLE;
        $this->id_column = 'report_id';
        $this->panel_name = _gettext('Reports');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_view_reports');
        $output_panel = new OutputPanelReports($this->domain, false);
        $output_panel->render([], false);
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

    public function dismiss(string $report_id): void
    {
        $entry_domain = $this->getEntryDomain($report_id);
        $this->verifyPermissions($entry_domain, 'perm_dismiss_reports');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "report_id" = ?');
        $this->database->executePrepared($prepared, [$report_id]);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm))
        {
            return;
        }

        switch ($perm)
        {
            case 'perm_view_reports':
                nel_derp(370, _gettext('You are not allowed to view reports.'));
                break;

            case 'perm_dismiss_reports':
                nel_derp(371, _gettext('You are not allowed to dismiss reports.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
