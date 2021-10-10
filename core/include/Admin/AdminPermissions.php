<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class AdminPermissions extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_PERMISSIONS_TABLE;
        $this->id_field = 'permission';
        $this->id_column = 'permission';
        $this->panel_name = _gettext('Permissions');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_permissions_manage');
        $output_panel = new \Nelliel\Output\OutputPanelPermissions($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_permissions_manage');
        $permission = $_POST['permission'];
        $description = $_POST['perm_description'];
        $prepared = $this->database->prepare(
                'INSERT INTO "' . $this->data_table . '" ("permission", "perm_description") VALUES (?, ?)');
        $this->database->executePrepared($prepared, [$permission, $description]);
        $this->outputMain(true);
    }

    public function editor(): void
    {
    }

    public function update(): void
    {
    }

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_permissions_manage');
        $id = $_GET[$this->id_field] ?? '';
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "permission" = ?');
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
            case 'perm_permissions_manage':
                nel_derp(365, _gettext('You are not allowed to manage permissions.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
