<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/staff_panel.php';

//
// Staff control panel
//
function nel_staff_panel($inputs)
{
    $authorize = nel_authorize();
    $temp_auth = array();
    $user = $authorize->getUser($_SESSION['username']);

    if (!$user->boardPerm('', 'perm_user_access') && !$user->boardPerm('', 'perm_role_access'))
    {
        nel_derp(340, _gettext('You are not allowed to access the staff panel.'));
    }

    if (is_null($inputs['section']) || $inputs['section'] === 'main')
    {
        nel_render_staff_panel_main();
    }
    else if ($inputs['section'] === 'user')
    {
        if (!$user->boardPerm('', 'perm_user_access'))
        {
            nel_derp(340, _gettext('You are not allowed to access the staff panel.'));
        }

        if ($inputs['action'] === 'new')
        {
            if (!$user->boardPerm('', 'perm_user_add'))
            {
                nel_derp(341, _gettext('You are not allowed to modify staff.'));
            }

            nel_render_staff_panel_user_edit(null);
            return;
        }

        if ($inputs['action'] === 'edit')
        {
            $user_id = (isset($_GET['user-id'])) ? $_GET['user-id'] : null;

            if (!$authorize->userExists($user_id))
            {
                nel_derp(440, _gettext('The specified user does not exist.'));
            }

            if (!$user->boardPerm('', 'perm_user_modify'))
            {
                nel_derp(341, _gettext('You are not allowed to modify staff.'));
            }

            nel_render_staff_panel_user_edit($user_id);
        }
        else if ($inputs['action'] === 'update')
        {
            $user_id = (isset($_POST['user_id'])) ? $_POST['user_id'] : null;

            if (!$authorize->userExists($user_id))
            {
                nel_derp(440, _gettext('The specified user does not exist.'));
            }

            $user = $authorize->getUser($user_id);

            if (!$user->boardPerm('', 'perm_user_modify'))
            {
                nel_derp(341, _gettext('You are not allowed to modify staff.'));
            }

            if (isset($_POST['user_password']) && !empty($_POST['user_password']))
            {
                $user->auth_data['user_password'] = nel_password_hash($_POST['user_password'], NEL_PASSWORD_ALGORITHM);
            }

            foreach ($_POST as $key => $value)
            {
                if (strpos($key, 'user_board_role') !== false)
                {
                    $board = substr($key, 16);

                    if($board === false)
                    {
                        $board = '';
                    }

                    if(!$user->boardRole($board))
                    {
                        $user->addUserRole($board, $value);
                        continue;
                    }

                    if ($value === '')
                    {
                        $user->removeUserRole($board, $value);
                    }
                    else
                    {
                        $user->updateUserRole($board, $value);
                    }

                    continue;
                }

                if ($key === 'user_password')
                {
                    continue;
                }

                $user->auth_data[$key] = $value;
            }

            $authorize->saveUsers();
            nel_render_staff_panel_user_edit($user_id);
        }
    }
    else if ($inputs['section'] === 'role')
    {
        if (!$user->boardPerm('', 'perm_role_access'))
        {
            nel_derp(342, _gettext('You are not allowed to modify roles.'));
        }

        if ($inputs['action'] === 'new')
        {
            if (!$user->boardPerm('', 'perm_role_add'))
            {
                nel_derp(341, _gettext('You are not allowed to add roles.'));
            }

            nel_render_staff_panel_role_edit(null);
            return;
        }

        if ($inputs['action'] === 'edit')
        {
            $role_id = (isset($_GET['role-id'])) ? $_GET['role-id'] : null;

            if (!$authorize->roleExists($role_id))
            {
                nel_derp(441, _gettext('The specified role does not exist.'));
            }

            if (!$user->boardPerm('', 'perm_role_modify'))
            {
                nel_derp(342, _gettext('You are not allowed to modify roles.'));
            }

            nel_render_staff_panel_role_edit($role_id);
        }
        else if ($inputs['action'] === 'update')
        {
            $role_id = (isset($_POST['role_id'])) ? $_POST['role_id'] : null;

            if (!$authorize->roleExists($role_id))
            {
                nel_derp(441, _gettext('The specified role does not exist.'));
            }

            $role = $authorize->getRole($role_id);

            if (!$user->boardPerm('', 'perm_role_modify'))
            {
                nel_derp(342, _gettext('You are not allowed to modify roles.'));
            }

            foreach ($_POST as $key => $value)
            {
                if (substr($key, 0, 5) === 'perm_')
                {
                    $value = ($value == 1) ? true : false;
                    $role->permissions->auth_data[$key] = $value;
                    continue;
                }

                $role->auth_data[$key] = $value;
            }

            $authorize->saveRoles();
            nel_render_staff_panel_role_edit($role_id);
        }
    }
    else
    {
        nel_derp(442, _gettext('No valid action given for user or role panels.'));
    }
}