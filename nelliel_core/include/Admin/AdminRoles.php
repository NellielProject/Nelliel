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
        $this->role_id = $_GET['role-id'] ?? null;

        if (!is_null($this->role_id) && !$this->authorization->roleExists($this->role_id))
        {
            nel_derp(231, _gettext('The specified role does not exist.'));
        }
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain, false);
        $output_panel->render(['section' => 'panel', 'user' => $this->session_user], false);
    }

    public function creator()
    {
        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain, false);
        $output_panel->render(['section' => 'edit', 'user' => $this->session_user, 'role_id' => $this->role_id], false);
        $this->outputMain(false);
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_roles'))
        {
            nel_derp(311, _gettext('You are not allowed to add roles.'));
        }

        $this->role_id = $_POST['role_id'];
        $this->update();
        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain, false);
        $output_panel->render(['section' => 'edit', 'user' => $this->session_user, 'role_id' => $this->role_id], false);
        $this->outputMain(true);
    }

    public function editor()
    {
        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain, false);
        $output_panel->render(['section' => 'edit', 'user' => $this->session_user, 'role_id' => $this->role_id], false);
        $this->outputMain(false);
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
            if (is_array($value))
            {
                $value = nel_form_input_default($value);
            }

            if (substr($key, 0, 5) === 'perm_')
            {
                $value = ($value == 1) ? true : false;
                $role->permissions->auth_data[$key] = $value;
                continue;
            }

            $role->auth_data[$key] = $value;
        }

        $this->authorization->saveRoles();
        $this->outputMain(true);
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_roles'))
        {
            nel_derp(313, _gettext('You are not allowed to remove roles.'));
        }

        $this->authorization->removeRole($this->role_id);
        $this->outputMain(true);
    }
}
