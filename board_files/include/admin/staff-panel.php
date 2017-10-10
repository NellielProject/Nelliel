<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Staff control panel
//
function nel_staff_panel($dataforce)
{
    $authorize = nel_get_authorization();
    $temp_auth = array();
    $mode = $dataforce['mode'];

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_user_access') && !$authorize->get_user_perm($_SESSION['username'], 'perm_role_access'))
    {
        nel_derp(102, array('origin' => 'ADMIN'));
    }

    require_once INCLUDE_PATH . 'output/staff-panel-generation.php';

    if (isset($_POST['user_id']))
    {
        $user_id = $_POST['user_id'];
    }

    if ($mode === 'admin->staff->main')
    {
        nel_render_staff_panel_main($dataforce);
    }
    else if ($mode === 'admin->staff->user->edit')
    {
        if (!$authorize->user_exists($user_id))
        {
            nel_derp(150, array('origin' => 'ADMIN'));
        }

        nel_render_staff_panel_user_edit($dataforce, $user_id);
    }
    else if ($mode === 'admin->staff->role->edit')
    {
        if (isset($_POST['role_id']))
        {
            $role_id = $_POST['role_id'];
        }

        nel_render_staff_panel_role_edit($dataforce, $role_id);
    }
    else if ($mode === 'admin->staff->user->update')
    {
        if (isset($_POST['user_id']))
        {
            $user_id = $_POST['user_id'];
        }
        else
        {
            return false; // TODO: No user entered error
        }

        if (isset($_POST['change_pass']) && isset($_POST['user_password']))
        {
            $authorize->update_user_info($user_id, 'user_password', nel_password_hash($_POST['user_password'], NELLIEL_PASS_ALGORITHM));
        }

        foreach ($_POST as $key => $value)
        {
            if($key === 'mode' || $key === 'user_password' || $key === 'change_pass')
            {
                continue;
            }

            $authorize->update_user_info($user_id, $key, $value);
        }

        nel_render_staff_panel_user_edit($dataforce, $user_id);
        return true;
    }
    else if ($mode === 'admin->staff->role->update')
    {
        if (isset($_POST['role_id']))
        {
            $role_id = $_POST['role_id'];
        }
        else
        {
            return false; // TODO: No role entered error
        }

        foreach ($_POST as $key => $value)
        {
            if($key === 'mode')
            {
                continue;
            }

            if(substr($key, 0, 5) === 'perm_')
            {
                $value = ($value === '1') ? true : false;
            }

            $authorize->update_perm($role_id, $key, $value);
        }

        nel_render_staff_panel_role_edit($dataforce, $role_id);
        return true;
    }
    else
    {
        ; // TODO: No valid operation given error here
    }
}