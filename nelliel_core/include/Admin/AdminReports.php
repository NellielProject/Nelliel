<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class AdminReports extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_REPORTS_TABLE;
        $this->id_field = 'report-id';
        $this->id_column = 'report_id';
        $this->panel_name = _gettext('Reports');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_reports_view');
        $output_panel = new \Nelliel\Output\OutputPanelReports($this->domain, false);
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

    public function remove(): void
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyPermissions($entry_domain, 'perm_reports_dismiss');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "report_id" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm))
        {
            return;
        }

        switch ($perm)
        {
            case 'perm_reports_view':
                nel_derp(370, _gettext('You are not allowed to view reports.'));
                break;

            case 'perm_reports_dismiss':
                nel_derp(371, _gettext('You are not allowed to dismiss reports.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
