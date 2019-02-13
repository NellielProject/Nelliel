<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

require_once INCLUDE_PATH . 'output/management/roles_panel.php';

class AdminRoles extends AdminHandler
{
    private $role_id;

    function __construct($database, Authorization $authorization, Domain $domain)
    {
        $this->database = $database;
        $this->authorization = $authorization;
        $this->domain = $domain;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session($this->authorization, true);
        $user = $session->sessionUser();
        $this->role_id = $_GET['role-id'] ?? null;

        if (!is_null($this->role_id) && !$this->authorization->roleExists($this->role_id))
        {
            nel_derp(231, _gettext('The specified role does not exist.'));
        }

        if ($inputs['action'] === 'new')
        {
            $this->creator($user);
        }
        else if ($inputs['action'] === 'add')
        {
            $this->add($user);
        }
        else if ($inputs['action'] === 'edit')
        {
            $this->editor($user);
        }
        else if ($inputs['action'] === 'update')
        {
            $this->update($user);
        }
        else if ($inputs['action'] === 'remove')
        {
            $this->remove($user);
        }

        $this->renderPanel($user);
    }

    public function renderPanel($user)
    {
        nel_render_roles_panel_main($user, $this->domain);
    }

    public function creator($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_role_modify'))
        {
            nel_derp(311, _gettext('You are not allowed to modify roles.'));
        }

        nel_render_roles_panel_edit($user, $this->domain, $this->role_id);
    }

    public function add($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_role_modify'))
        {
            nel_derp(311, _gettext('You are not allowed to modify roles.'));
        }

        $this->role_id = $_POST['role_id'];
        $this->update($user);
        nel_render_roles_panel_edit($user, $this->domain, $this->role_id);
    }

    public function editor($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_role_modify'))
        {
            nel_derp(311, _gettext('You are not allowed to modify roles.'));
        }

        nel_render_roles_panel_edit($user, $this->domain, $this->role_id);
    }

    public function update($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_role_modify'))
        {
            nel_derp(312, _gettext('You are not allowed to modify roles.'));
        }

        $role = $this->authorization->newRole($this->role_id);
        $role->setupNew();

        foreach ($_POST as $key => $value)
        {
            if (substr($key, 0, 5) === 'perm_')
            {
                $value = ($value == 1) ? true : false;
                $role->permissions->auth_data[$key] = $value;
                continue;
            }

            if($key === 'super_admin' && !$user->isSuperAdmin())
            {
                nel_derp(232, _gettext('You cannot create or modify Super Admin users.'));
            }

            $role->auth_data[$key] = $value;
        }

        $this->authorization->saveRoles();
        nel_render_roles_panel_edit($user, $this->domain, $this->role_id);
    }

    public function remove($user)
    {
        $this->authorization->removeRole($this->role_id);
    }
}
