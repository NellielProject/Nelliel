<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/staff_panel.php';

//
// Staff control panel
//
function nel_staff_panel($section, $action)
{
    $authorize = nel_authorize();
    $temp_auth = array();

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_user_access') &&
         !$authorize->get_user_perm($_SESSION['username'], 'perm_role_access'))
    {
        nel_derp(340, _gettext('You are not allowed to access the staff panel.'));
    }

    if (is_null($section) || $section === 'main')
    {
        nel_render_staff_panel_main();
    }
    else if ($section === 'user')
    {
        if (!$authorize->get_user_perm($_SESSION['username'], 'perm_user_access'))
        {
            nel_derp(340, _gettext('You are not allowed to access the staff panel.'));
        }

        if ($action === 'new')
        {
            if (!$authorize->get_user_perm($_SESSION['username'], 'perm_user_add'))
            {
                nel_derp(341, _gettext('You are not allowed to modify staff.'));
            }

            nel_render_staff_panel_user_edit(null);
            return;
        }

        $user_id = (isset($_POST['user_id'])) ? $_POST['user_id'] : null;

        if (!$authorize->user_exists($user_id))
        {
            nel_derp(440, _gettext('The specified user does not exist.'));
        }

        if ($action === 'edit')
        {
            if (!$authorize->get_user_perm($_SESSION['username'], 'perm_user_modify'))
            {
                nel_derp(341, _gettext('You are not allowed to modify staff.'));
            }

            nel_render_staff_panel_user_edit($user_id);
        }
        else if ($action === 'update')
        {
            if (!$authorize->get_user_perm($_SESSION['username'], 'perm_user_modify'))
            {
                nel_derp(341, _gettext('You are not allowed to modify staff.'));
            }

            if (isset($_POST['user_password']) && !empty($_POST['user_password']))
            {
                $authorize->update_user_info($user_id, 'user_password', nel_password_hash($_POST['user_password'], NEL_PASSWORD_ALGORITHM));
            }

            foreach ($_POST as $key => $value)
            {
                if (strpos($key, 'user_board_role') !== false)
                {
                    $board = substr($key, 16);
                    $remove = false;

                    if ($value === '')
                    {
                        $remove = true;
                    }

                    $all_boards = 0;

                    if ($board == '')
                    {
                        $all_boards = 1;
                    }

                    $update = array('user_id' => $user_id, 'role_id' => $value, 'board' => $board,
                        'all_boards' => $all_boards);
                    $authorize->update_user_role($user_id, $update, $board, $remove);
                    continue;
                }

                if ($key === 'action' || $key === 'user_password' || $key === 'board_id')
                {
                    continue;
                }

                $authorize->update_user_info($user_id, $key, $value);
            }

            $authorize->save_users();
            $authorize->save_user_roles();
            nel_render_staff_panel_user_edit($user_id);
        }
    }
    else if ($section === 'role')
    {
        if (!$authorize->get_user_perm($_SESSION['username'], 'perm_role_access'))
        {
            nel_derp(342, _gettext('You are not allowed to modify roles.'));
        }

        if ($action === 'new')
        {
            if (!$authorize->get_user_perm($_SESSION['username'], 'perm_role_add'))
            {
                nel_derp(341, _gettext('You are not allowed to modify staff.'));
            }

            nel_render_staff_panel_role_edit(null);
            return;
        }

        $role_id = (isset($_POST['role_id'])) ? $_POST['role_id'] : null;

        if (!$authorize->role_exists($role_id))
        {
            nel_derp(441, _gettext('The specified role does not exist.'));
        }

        if ($action === 'edit')
        {
            if (!$authorize->get_user_perm($_SESSION['username'], 'perm_role_modify'))
            {
                nel_derp(342, _gettext('You are not allowed to modify roles.'));
            }

            nel_render_staff_panel_role_edit($role_id);
        }
        else if ($action === 'update')
        {
            if (!$authorize->get_user_perm($_SESSION['username'], 'perm_role_modify'))
            {
                nel_derp(342, _gettext('You are not allowed to modify roles.'));
            }

            foreach ($_POST as $key => $value)
            {
                if ($key === 'action')
                {
                    continue;
                }

                if (substr($key, 0, 5) === 'perm_')
                {
                    $value = ($value == 1) ? true : false;
                }

                $authorize->update_perm($role_id, $key, $value);
            }

            $authorize->save_roles();
            nel_render_staff_panel_role_edit($role_id);
        }
    }
    else
    {
        nel_derp(442, _gettext('No valid action given for user or role panels.'));
    }
}