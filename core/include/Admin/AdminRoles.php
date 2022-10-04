<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelRoles;

class AdminRoles extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_ROLES_TABLE;
        $this->id_column = 'role_id';
        $this->panel_name = _gettext('Roles');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_view_roles');
        $output_panel = new OutputPanelRoles($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_roles');
        $output_panel = new OutputPanelRoles($this->domain, false);
        $output_panel->new([], false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_roles');
        $this->update($_POST['role_id'] ?? '');
    }

    public function editor(string $role_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_roles');
        $output_panel = new OutputPanelRoles($this->domain, false);
        $output_panel->edit(['role_id' => $role_id], false);
    }

    public function update(string $role_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_roles');
        $role = $this->authorization->getRole($role_id);

        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                $value = nel_form_input_default($value);
            }

            if (utf8_substr($key, 0, 5) === 'perm_') {
                $value = ($value == 1) ? true : false;
                $role->permissions->changeData($key, $value);
                continue;
            }

            $role->changeData($key, $value);
        }

        $this->authorization->saveRoles();
        $this->panel();
    }

    public function delete(string $role_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_roles');
        $this->authorization->removeRole($role_id);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_view_roles':
                nel_derp(375, _gettext('You are not allowed to view roles.'));
                break;

            case 'perm_manage_roles':
                nel_derp(376, _gettext('You are not allowed to manage roles.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
