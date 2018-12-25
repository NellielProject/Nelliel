<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/users_panel.php';

class AdminUsers extends AdminBase
{
    private $domain;
    private $user_id;

    function __construct($database, $authorization, $domain)
    {
        $this->database = $database;
        $this->authorization = $authorization;
        $this->domain = $domain;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session($this->authorization, true);
        $user = $session->sessionUser();
        $this->user_id = (isset($_GET['user-id'])) ? $_GET['user-id'] : null;

        if (!is_null($this->user_id) && !$this->authorization->userExists($this->user_id))
        {
            nel_derp(230, _gettext('The specified user does not exist.'));
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

        $this->renderPanel($user);
    }

    public function renderPanel($user)
    {
        nel_render_users_panel_main($user, $this->domain);
    }

    public function creator($user)
    {
        if (!$user->boardPerm('', 'perm_user_modify'))
        {
            nel_derp(301, _gettext('You are not allowed to modify users.'));
        }

        nel_render_users_panel_edit($user, $this->domain, $this->user_id);
    }

    public function add($user)
    {
        if (!$user->boardPerm('', 'perm_user_modify'))
        {
            nel_derp(301, _gettext('You are not allowed to modify users.'));
        }

        $this->user_id = $_POST['user_id'];
        $this->update($user);
        nel_render_users_panel_edit($user, $this->domain, $this->user_id);
    }

    public function editor($user) // GG
    {
        if (!$user->boardPerm('', 'perm_user_modify'))
        {
            nel_derp(301, _gettext('You are not allowed to modify users.'));
        }

        nel_render_users_panel_edit($user, $this->domain, $this->user_id);
    }

    public function update($user) // GG
    {
        if (!$user->boardPerm('', 'perm_user_modify'))
        {
            nel_derp(301, _gettext('You are not allowed to modify users.'));
        }

        if(!$this->authorization->userExists($this->user_id))
        {
            $update_user = $this->authorization->newUser($this->user_id);
        }
        else
        {
            $update_user = $this->authorization->getUser($this->user_id);
            $update_user->loadFromDatabase();
        }

        foreach ($_POST as $key => $value)
        {
            if (strpos($key, 'user_board_role') !== false)
            {
                $board_id = substr($key, 16);

                if ($board_id === false)
                {
                    $board_id = '';
                }

                if (!$update_user->boardRole($board_id))
                {
                    $update_user->changeOrAddBoardRole($board_id, $value);
                    continue;
                }

                if ($value === '')
                {
                    $update_user->removeBoardRole($board_id, $value);
                }
                else
                {
                    $update_user->ChangeOrAddBoardRole($board_id, $value);
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
        nel_render_users_panel_edit($user, $this->domain, $this->user_id);
    }

    public function remove($user)
    {
    }
}
