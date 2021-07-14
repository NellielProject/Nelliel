<?php

declare(strict_types=1);

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminReports extends Admin
{
    private $defaults = false;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function renderPanel()
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Render\OutputPanelReports($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
    }

    public function add()
    {
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        $this->verifyAction($this->domain);
        $report_id = $_GET['report-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_REPORTS_TABLE . '" WHERE "report_id" = ?');
        $this->database->executePrepared($prepared, [$report_id]);
        $this->outputMain(true);
    }

    public function enable()
    {
        $this->verifyAction($this->domain);
    }

    public function disable()
    {
        $this->verifyAction($this->domain);
    }

    public function makeDefault()
    {
        $this->verifyAction($this->domain);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_reports'))
        {
            nel_derp(380, _gettext('You do not have access to the Reports panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_reports'))
        {
            nel_derp(381, _gettext('You are not allowed to manage reports.'));
        }
    }
}
