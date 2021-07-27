<?php

declare(strict_types=1);

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
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain, false);
        $output_panel->new(['role_id' => $this->role_id], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $this->verifyAction($this->domain);
        $this->role_id = $_POST['role_id'] ?? '';
        $this->update();
        $this->outputMain(true);
    }

    public function editor(): void
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Output\OutputPanelRoles($this->domain, false);
        $output_panel->edit(['role_id' => $this->role_id], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $this->verifyAction($this->domain);
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
        $this->verifyAction($this->domain);
        $this->authorization->removeRole($this->role_id);
        $this->outputMain(true);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_access_roles'))
        {
            nel_derp(310, _gettext('You do not have access to the Roles panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_roles'))
        {
            nel_derp(311, _gettext('You are not allowed to manage roles.'));
        }
    }
}
