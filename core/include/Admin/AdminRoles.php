<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminRoles extends Admin
{
    private $role_id;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->role_id = $_GET['role-id'] ?? '';
        $this->data_table = NEL_ROLES_TABLE;
        $this->id_field = 'role-id';
        $this->id_column = 'role_id';
        $this->panel_name = _gettext('Roles');

        if (!$this->authorization->roleExists($this->role_id))
        {
            nel_derp(231, _gettext('The specified role does not exist.'));
        }
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_roles_view');
        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_roles_manage');
        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain, false);
        $output_panel->new(['role_id' => $this->role_id], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_roles_manage');
        $this->role_id = utf8_strtolower($_POST['role_id'] ?? '');
        $this->update();
        $this->outputMain(true);
    }

    public function editor(): void
    {
        $this->verifyPermissions($this->domain, 'perm_roles_manage');
        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain, false);
        $output_panel->edit(['role_id' => $this->role_id], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $this->verifyPermissions($this->domain, 'perm_roles_manage');
        $role = $this->authorization->newRole($this->role_id);
        $role->setupNew();

        foreach ($_POST as $key => $value)
        {
            if (is_array($value))
            {
                $value = nel_form_input_default($value);
            }

            if (utf8_substr($key, 0, 5) === 'perm_')
            {
                $value = ($value == 1) ? true : false;
                $role->permissions->changeData($key, $value);
                continue;
            }

            $role->changeData($key, $value);
        }

        $this->authorization->saveRoles();
        $this->outputMain(true);
    }

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_roles_manage');
        $this->authorization->removeRole($this->role_id);
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
            case 'perm_roles_view':
                nel_derp(375, _gettext('You are not allowed to view roles.'));
                break;

            case 'perm_roles_manage':
                nel_derp(376, _gettext('You are not allowed to manage roles.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
