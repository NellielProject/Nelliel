<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminRoles extends AdminHandler
{
    private $role_id;

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->validateUser();
    }

    public function actionDispatch($inputs)
    {
        $this->role_id = $_GET['role-id'] ?? null;

        if (!is_null($this->role_id) && !$this->authorization->roleExists($this->role_id))
        {
            nel_derp(231, _gettext('The specified role does not exist.'));
        }

        if ($inputs['action'] === 'new')
        {
            $this->creator();
        }
        else if ($inputs['action'] === 'add')
        {
            $this->add();
        }
        else if ($inputs['action'] === 'edit')
        {
            $this->editor();
        }
        else if ($inputs['action'] === 'update')
        {
            $this->update();
        }
        else if ($inputs['action'] === 'remove')
        {
            $this->remove();
        }
        else
        {
            $this->renderPanel();
        }
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain);
        $output_panel->render(['section' => 'panel', 'user' => $this->session_user], false);
    }

    public function creator()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_roles'))
        {
            nel_derp(311, _gettext('You are not allowed to modify roles.'));
        }

        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain);
        $output_panel->render(['section' => 'edit', 'user' => $this->session_user, 'role_id' => $this->role_id], false);
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_roles'))
        {
            nel_derp(311, _gettext('You are not allowed to modify roles.'));
        }

        $this->role_id = $_POST['role_id'];
        $this->update();
        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain);
        $output_panel->render(['section' => 'edit', 'user' => $this->session_user, 'role_id' => $this->role_id], false);
    }

    public function editor()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_roles'))
        {
            nel_derp(311, _gettext('You are not allowed to modify roles.'));
        }

        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain);
        $output_panel->render(['section' => 'edit', 'user' => $this->session_user, 'role_id' => $this->role_id], false);
    }

    public function update()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_roles'))
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

            if($key === 'super_admin' && !$this->session_user->isSuperAdmin())
            {
                nel_derp(232, _gettext('You cannot create or modify Super Admin users.'));
            }

            $role->auth_data[$key] = $value;
        }

        $this->authorization->saveRoles();
        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain);
        $output_panel->render(['section' => 'edit', 'user' => $this->session_user, 'role_id' => $this->role_id], false);
    }

    public function remove()
    {
        $this->authorization->removeRole($this->role_id);
        $this->renderPanel();
    }
}
