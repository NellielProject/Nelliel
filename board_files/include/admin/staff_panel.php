<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/staff_panel.php';

//
// Staff control panel
//
function nel_staff_panel($dataforce)
{
    $authorize = nel_authorize();
    $temp_auth = array();

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_user_access', INPUT_BOARD_ID) &&
    !$authorize->get_user_perm($_SESSION['username'], 'perm_role_access', INPUT_BOARD_ID) &&
         !$authorize->get_user_perm($_SESSION['username'], 'perm_all_user_access') &&
         !$authorize->get_user_perm($_SESSION['username'], 'perm_all_role_access'))
    {
        nel_derp(340, nel_stext('ERROR_340'));
    }

    if ($dataforce['mode_segments'][2] === 'main')
    {
        nel_render_staff_panel_main($dataforce);
    }
    else if ($dataforce['mode_segments'][2] === 'user')
    {
        if (!$authorize->get_user_perm($_SESSION['username'], 'perm_user_access', INPUT_BOARD_ID) &&
             !$authorize->get_user_perm($_SESSION['username'], 'perm_all_user_access'))
        {
            nel_derp(341, nel_stext('ERROR_341'));
        }

        $user_id = (isset($_POST['user_id'])) ? $_POST['user_id'] : null;

        if (!$authorize->user_exists($user_id))
        {
            nel_derp(440, nel_stext('ERROR_440'));
        }

        if ($dataforce['mode_segments'][3] === 'edit')
        {
            nel_render_staff_panel_user_edit($dataforce, $user_id);
        }
        else if ($dataforce['mode_segments'][3] === 'update')
        {
            if (isset($_POST['change_pass']) && isset($_POST['user_password']))
            {
                $authorize->update_user_info($user_id, 'user_password', nel_password_hash($_POST['user_password'], NELLIEL_PASS_ALGORITHM));
            }

            foreach ($_POST as $key => $value)
            {
                if ($key === 'mode' || $key === 'user_password' || $key === 'change_pass')
                {
                    continue;
                }

                $authorize->update_user_info($user_id, $key, $value);
            }

            nel_render_staff_panel_user_edit($dataforce, $user_id);
        }
    }
    else if ($dataforce['mode_segments'][2] === 'role')
    {
        if (!$authorize->get_user_perm($_SESSION['username'], 'perm_role_access') &&
             !$authorize->get_user_perm($_SESSION['username'], 'perm_all_role_access'))
        {
            nel_derp(342, nel_stext('ERROR_342'));
        }

        $role_id = (isset($_POST['role_id'])) ? $_POST['role_id'] : null;

        if (!$authorize->role_exists($role_id))
        {
            nel_derp(441, nel_stext('ERROR_441'));
        }

        if ($dataforce['mode_segments'][3] === 'edit')
        {
            nel_render_staff_panel_role_edit($dataforce, $role_id);
        }
        else if ($dataforce['mode_segments'][3] === 'update')
        {
            foreach ($_POST as $key => $value)
            {
                if ($key === 'mode')
                {
                    continue;
                }

                if (substr($key, 0, 5) === 'perm_')
                {
                    $value = ($value == 1) ? true : false;
                }

                $authorize->update_perm($role_id, $key, $value);
            }

            nel_render_staff_panel_role_edit($dataforce, $role_id);
        }
    }
    else
    {
        nel_derp(442, nel_stext('ERROR_442'));
    }
}