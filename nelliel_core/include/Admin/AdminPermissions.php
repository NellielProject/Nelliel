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

class AdminPermissions extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function renderPanel()
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Render\OutputPanelPermissions($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator()
    {
        $this->verifyAccess($this->domain);
    }

    public function add()
    {
        $this->verifyAction($this->domain);
        $permission = $_POST['permission'];
        $description = $_POST['perm_description'];
        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_PERMISSIONS_TABLE . '" ("permission", "perm_description") VALUES (?, ?)');
        $this->database->executePrepared($prepared, [$permission, $description]);
        $this->outputMain(true);
    }

    public function editor()
    {
        $this->verifyAccess($this->domain);
    }

    public function update()
    {
        $this->verifyAction($this->domain);
    }

    public function remove()
    {
        $this->verifyAction($this->domain);
        $permission = $_GET['permission'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_PERMISSIONS_TABLE . '" WHERE "permission" = ?');
        $this->database->executePrepared($prepared, [$permission]);
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
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_permissions'))
        {
            nel_derp(420, _gettext('You do not have access to the Permissions panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_permissions'))
        {
            nel_derp(421, _gettext('You are not allowed to manage permissions.'));
        }
    }
}
