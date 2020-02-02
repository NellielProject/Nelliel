<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminUsers extends AdminHandler
{
    private $user_id;

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->validateUser();
    }

    public function actionDispatch($inputs)
    {
        $this->user_id = $_GET['user-id'] ?? null;

        if (!is_null($this->user_id) && !$this->authorization->userExists($this->user_id))
        {
            nel_derp(230, _gettext('The specified user does not exist.'));
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
        $output_panel = new \Nelliel\Output\OutputPanelUsers($this->domain);
        $output_panel->render(['section' => 'panel', 'user' => $this->session_user], false);
    }

    public function creator()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_users'))
        {
            nel_derp(301, _gettext('You are not allowed to modify users.'));
        }

        $output_panel = new \Nelliel\Output\OutputPanelUsers($this->domain);
        $output_panel->render(['section' => 'edit', 'user' => $this->session_user, 'user_id' => $this->user_id], false);
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_users'))
        {
            nel_derp(301, _gettext('You are not allowed to modify users.'));
        }

        $this->user_id = $_POST['user_id'];
        $this->update();
        $output_panel = new \Nelliel\Output\OutputPanelUsers($this->domain);
        $output_panel->render(['section' => 'edit', 'user' => $this->session_user, 'user_id' => $this->user_id], false);
    }

    public function editor()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_users'))
        {
            nel_derp(301, _gettext('You are not allowed to modify users.'));
        }

        $output_panel = new \Nelliel\Output\OutputPanelUsers($this->domain);
        $output_panel->render(['section' => 'edit', 'user' => $this->session_user, 'user_id' => $this->user_id], false);
    }

    public function update()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_users'))
        {
            nel_derp(301, _gettext('You are not allowed to modify users.'));
        }

        if (!$this->authorization->userExists($this->user_id))
        {
            $update_user = $this->authorization->newUser($this->user_id);
        }
        else
        {
            $update_user = $this->authorization->getUser($this->user_id);
            $update_user->loadFromDatabase();
        }

        foreach ($_POST as $key => $value) // TODO: Improve this
        {
            if (strpos($key, 'board_role') !== false || $key === 'site_role')
            {
                if ($key === 'site_role')
                {
                    $domain = new \Nelliel\DomainSite($this->database);
                }
                else
                {
                    $domain = new \Nelliel\DomainBoard(substr($key, 11), $this->database);
                }

                //$domain = new \Nelliel\DomainBoard($role_domain, $this->database);

                if ($value === '')
                {
                    $update_user->removeRole($domain->id(), $value);
                }
                else
                {
                    $update_user->ChangeOrAddRole($domain->id(), $value);
                }

                continue;
            }

            if ($key === 'user_password')
            {
                if (!empty($value))
                {
                    $update_user->auth_data['user_password'] = nel_password_hash($value, NEL_PASSWORD_ALGORITHM);
                }

                continue;
            }

            $update_user->auth_data[$key] = $value;
        }

        $this->authorization->saveUsers();
        $output_panel = new \Nelliel\Output\OutputPanelUsers($this->domain);
        $output_panel->render(['section' => 'edit', 'user' => $this->session_user, 'user_id' => $this->user_id], false);
    }

    public function remove()
    {
        $this->authorization->removeUser($this->user_id);
    }
}
