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

class AdminRoles extends Admin
{
    private $role_id;

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        parent::__construct($authorization, $domain, $session, $inputs);
        $this->role_id = $_GET['role-id'] ?? null;

        if (!is_null($this->role_id) && !$this->authorization->roleExists($this->role_id))
        {
            nel_derp(231, _gettext('The specified role does not exist.'));
        }
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelRoles($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelRoles($this->domain, false);
        $output_panel->new(['role_id' => $this->role_id], false);
        $this->outputMain(false);
    }

    public function add()
    {
        $this->verifyAction();
        $this->role_id = $_POST['role_id'];
        $this->update();
        $this->outputMain(true);
    }

    public function editor()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelRoles($this->domain, false);
        $output_panel->edit(['role_id' => $this->role_id], false);
        $this->outputMain(false);
    }

    public function update()
    {
        $this->verifyAction();
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
                $role->permissions->change($key, $value);
                continue;
            }

            $role->auth_data[$key] = $value;
        }

        $this->authorization->saveRoles();
        $this->outputMain(true);
    }

    public function remove()
    {
        $this->verifyAction();
        $this->authorization->removeRole($this->role_id);
        $this->outputMain(true);
    }

    public function enable()
    {
        $this->verifyAction();
    }

    public function disable()
    {
        $this->verifyAction();
    }

    public function makeDefault()
    {
        $this->verifyAction();
    }

    public function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_access_roles'))
        {
            nel_derp(310, _gettext('You do not have access to the Roles panel.'));
        }
    }

    public function verifyAction()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_roles'))
        {
            nel_derp(311, _gettext('You are not allowed to manage roles.'));
        }
    }
}
