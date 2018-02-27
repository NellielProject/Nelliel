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
        echo "wat";
        var_dump($_SESSION);
        nel_derp(340, nel_stext('ERROR_340'));
    }

    if (is_null($section) || $section === 'main')
    {
        nel_render_staff_panel_main();
    }
    else if ($section === 'user')
    {
        if (!$authorize->get_user_perm($_SESSION['username'], 'perm_user_access'))
        {
            nel_derp(340, nel_stext('ERROR_340'));
        }

        if ($action === 'new')
        {
            if (!$authorize->get_user_perm($_SESSION['username'], 'perm_user_add'))
            {
                nel_derp(341, nel_stext('ERROR_341'));
            }

            nel_render_staff_panel_user_edit(null);
            return;
        }

        $user_id = (isset($_POST['user_id'])) ? $_POST['user_id'] : null;

        if (!$authorize->user_exists($user_id))
        {
            nel_derp(440, nel_stext('ERROR_440'));
        }

        if ($action === 'edit')
        {
            if (!$authorize->get_user_perm($_SESSION['username'], 'perm_user_modify'))
            {
                nel_derp(341, nel_stext('ERROR_341'));
            }

            nel_render_staff_panel_user_edit($user_id);
        }
        else if ($action === 'update')
        {
            if (!$authorize->get_user_perm($_SESSION['username'], 'perm_user_modify'))
            {
                nel_derp(341, nel_stext('ERROR_341'));
            }

            if (isset($_POST['change_pass']) && isset($_POST['user_password']))
            {
                $authorize->update_user_info($user_id, 'user_password', nel_password_hash($_POST['user_password'], NELLIEL_PASS_ALGORITHM));
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

                if ($key === 'action' || $key === 'user_password' || $key === 'change_pass' || $key === 'board_id')
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
            nel_derp(342, nel_stext('ERROR_342'));
        }

        if ($action === 'new')
        {
            if (!$authorize->get_user_perm($_SESSION['username'], 'perm_role_add'))
            {
                nel_derp(341, nel_stext('ERROR_341'));
            }

            nel_render_staff_panel_role_edit(null);
            return;
        }

        $role_id = (isset($_POST['role_id'])) ? $_POST['role_id'] : null;

        if (!$authorize->role_exists($role_id))
        {
            nel_derp(441, nel_stext('ERROR_441'));
        }

        if ($action === 'edit')
        {
            if (!$authorize->get_user_perm($_SESSION['username'], 'perm_role_modify'))
            {
                nel_derp(342, nel_stext('ERROR_342'));
            }

            nel_render_staff_panel_role_edit($role_id);
        }
        else if ($action === 'update')
        {
            if (!$authorize->get_user_perm($_SESSION['username'], 'perm_role_modify'))
            {
                nel_derp(342, nel_stext('ERROR_342'));
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
        nel_derp(442, nel_stext('ERROR_442'));
    }
}