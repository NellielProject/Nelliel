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

class AdminPermissions extends Admin
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
        $output_panel = new \Nelliel\Render\OutputPanelPermissions($this->domain, false);
        $output_panel->render([], false);
        $this->data_table = NEL_PERMISSIONS_TABLE;
        $this->id_field = 'permission';
        $this->id_column = 'permission';
    }

    public function creator()
    {
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
    }

    public function update()
    {
    }

    public function remove()
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
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
