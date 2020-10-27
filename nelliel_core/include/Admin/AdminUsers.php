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
        $this->user_id = $_GET['user-id'] ?? null;

        if (!is_null($this->user_id) && !$this->authorization->userExists($this->user_id))
        {
            nel_derp(230, _gettext('The specified user does not exist.'));
        }
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelUsers($this->domain, false);
        $output_panel->render(['section' => 'panel', 'user' => $this->session_user], false);
    }

    public function creator()
    {
        $output_panel = new \Nelliel\Output\OutputPanelUsers($this->domain, false);
        $output_panel->render(['section' => 'edit', 'user' => $this->session_user, 'user_id' => $this->user_id], false);
        $this->outputMain(false);
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_users'))
        {
            nel_derp(301, _gettext('You are not allowed to add users.'));
        }

        $this->user_id = $_POST['user_id'];
        $this->update();
        $this->outputMain(true);
    }

    public function editor()
    {
        $output_panel = new \Nelliel\Output\OutputPanelUsers($this->domain, false);
        $output_panel->render(['section' => 'edit', 'user' => $this->session_user, 'user_id' => $this->user_id], false);
        $this->outputMain(false);
    }

    public function update()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_users'))
        {
            nel_derp(302, _gettext('You are not allowed to modify users.'));
        }

        $update_user = $this->authorization->getUser($this->user_id);

        foreach ($_POST as $key => $value) // TODO: Improve this
        {
            if (is_array($value))
            {
                $value = nel_form_input_default($value);
            }

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

                if ($value === '')
                {
                    $update_user->removeRole($domain->id(), $value);
                }
                else
                {
                    $update_user->modifyRole($domain->id(), $value);
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
        $update_user->loadFromDatabase();
        $this->outputMain(true);
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_users'))
        {
            nel_derp(303, _gettext('You are not allowed to remove users.'));
        }

        $this->authorization->removeUser($this->user_id);
        $this->outputMain(true);
    }
}
