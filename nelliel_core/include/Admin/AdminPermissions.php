<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminPermissions extends AdminHandler
{

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->validateUser();
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelPermissions($this->domain, false);
        $output_panel->render(['user' => $this->session_user], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_permissions_modify'))
        {
            nel_derp(451, _gettext('You are not allowed to add permissions.'));
        }

        $permission = $_POST['permission'];
        $description = $_POST['description'];
        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_PERMISSIONS_TABLE . '" ("permission", "description") VALUES (?, ?)');
        $this->database->executePrepared($prepared, [$permission, $description]);
        $admin_handler->outputMain(true);
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_permissions_modify'))
        {
            nel_derp(452, _gettext('You are not allowed to remove permissions.'));
        }

        $permission = $_GET['permission'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_PERMISSIONS_TABLE . '" WHERE "permission" = ?');
        $this->database->executePrepared($prepared, [$permission]);
        $admin_handler->outputMain(true);
    }
}
