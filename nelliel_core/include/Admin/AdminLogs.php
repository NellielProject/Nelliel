<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Auth\Authorization;

class AdminLogs extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, array $inputs)
    {
        parent::__construct($authorization, $domain, $inputs);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelLogs($this->domain, false);
        $log_type = $_GET['log-type'] ?? '';
        $output_panel->render(['log_type' => $log_type], false);
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
    }

    private function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_logs'))
        {
            nel_derp(341, _gettext('You are not allowed to access the logs panel.'));
        }
    }
}
