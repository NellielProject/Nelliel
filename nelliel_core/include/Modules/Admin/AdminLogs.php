<?php

declare(strict_types=1);

namespace Nelliel\Modules\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Modules\Account\Session;
use Nelliel\Auth\Authorization;

class AdminLogs extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel()
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Modules\Output\OutputPanelLogs($this->domain, false);
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

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_access_logs'))
        {
            nel_derp(470, _gettext('You do not have access to the Logs panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_logs'))
        {
            nel_derp(471, _gettext('You are not allowed to manage logs.'));
        }
    }
}
