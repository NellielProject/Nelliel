<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelPermissions;

class AdminPermissions extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_PERMISSIONS_TABLE;
        $this->id_column = 'permission';
        $this->panel_name = _gettext('Permissions');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_permissions_manage');
        $output_panel = new OutputPanelPermissions($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {}

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_permissions_manage');
        $permission = $_POST['permission'];
        $description = $_POST['description'];
        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table . '" ("permission", "description") VALUES (?, ?)');
        $this->database->executePrepared($prepared, [$permission, $description]);
        $this->panel();
    }

    public function editor(): void
    {}

    public function update(): void
    {}

    public function delete(string $permission): void
    {
        $this->verifyPermissions($this->domain, 'perm_permissions_manage');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "permission" = ?');
        $this->database->executePrepared($prepared, [$permission]);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_permissions_manage':
                nel_derp(365, _gettext('You are not allowed to manage permissions.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
