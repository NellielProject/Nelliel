<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Staff control panel
//
function nel_staff_panel($dataforce, $authorize, $plugins)
{
    $temp_auth = array();
    $mode = $dataforce['mode_action'];

    if (!$authorize->get_user_perm($_SESSION['username'], 'perm_staff_panel'))
    {
        nel_derp(102, array('origin' => 'ADMIN'));
    }

    require_once INCLUDE_PATH . 'output/staff-panel-generation.php';

    if (isset($_POST['staff_name']))
    {
        $staff_name = $_POST['staff_name'];
    }

    if ($mode === 'edit' || $mode === 'add')
    {
        if (isset($_POST['staff_type']))
        {
            $staff_type = $_POST['staff_type'];
        }

        if ($mode === 'add')
        {
            if ($authorize->get_user_auth($_POST['staff_name']))
            {
                nel_derp(154, array('origin' => 'ADMIN'));
            }

            nel_gen_new_staff($staff_name, $staff_type, $authorize);
        }
        else if ($mode === 'edit')
        {
            if (!$authorize->get_user_auth($staff_name))
            {
                nel_derp(150, array('origin' => 'ADMIN'));
            }
        }

        $temp_auth = $authorize->get_user_auth($staff_name);
        nel_render_staff_panel_edit($dataforce, $temp_auth);
    }
    else if ($mode === 'update')
    {
        $old_pass = $authorize->get_user_setting($staff_name, 'staff_password');
        $new_pass = '';
        $new_auth = $authorize->get_blank_settings();

        foreach ($_POST as $key => $val)
        {
            if ($key === 'staff_password')
            {
                $new_pass = nel_password_hash($val, NELLIEL_PASS_ALGORITHM);
            }

            if ($key === 'change_pass' && $new_pass != '')
            {
                $new_auth['staff_password'] = $new_pass;
            }

            if ($key !== 'mode' && $key !== 'staff_name' && $key !== 'username' && $key !== 'super_sekrit' && $key !== 'staff_password' && $key !== 'change_pass')
            {
                if ($val === '1')
                {
                    $new_auth[$key] = TRUE;
                }
                else
                {
                    $new_auth[$key] = $val;
                }
            }

            $authorize->update_user_auth($staff_name, $new_auth, $authorize);
            $temp_auth = $new_auth;
        }

        $authorize->write_auth_file();
        nel_render_staff_panel_add($dataforce, $temp_auth);
    }
    else if ($mode === 'delete')
    {
        $authorize->remove_user_auth($staff_name);
        $authorize->write_auth_file();
        nel_render_staff_panel_add($dataforce, $temp_auth);
    }
    else if ($mode == 'panel')
    {
        nel_render_staff_panel_add($dataforce, $temp_auth);
    }
    else
    {
        ; // error here
    }
}

function nel_gen_new_staff($new_name, $new_type, $authorize)
{
    $new_auth = $authorize->get_blank_settings();

    if ($new_type === 'admin')
    {
        $authorize->update_user_setting($new_name, 'perm_config', TRUE);
        $authorize->update_user_setting($new_name, 'perm_staff_panel', TRUE);
        $authorize->update_user_setting($new_name, 'perm_ban_panel', TRUE);
        $authorize->update_user_setting($new_name, 'perm_thread_panel', TRUE);
        $authorize->update_user_setting($new_name, 'perm_mod_mode', TRUE);
        $authorize->update_user_setting($new_name, 'perm_ban', TRUE);
        $authorize->update_user_setting($new_name, 'perm_delete', TRUE);
        $authorize->update_user_setting($new_name, 'perm_post', TRUE);
        $authorize->update_user_setting($new_name, 'perm_post_anon', TRUE);
        $authorize->update_user_setting($new_name, 'perm_sticky', TRUE);
        $authorize->update_user_setting($new_name, 'perm_update_pages', TRUE);
        $authorize->update_user_setting($new_name, 'perm_update_cache', TRUE);
    }
    else if ($new_type === 'moderator')
    {
        $authorize->update_user_setting($new_name, 'perm_config', FALSE);
        $authorize->update_user_setting($new_name, 'perm_staff_panel', FALSE);
        $authorize->update_user_setting($new_name, 'perm_ban_panel', TRUE);
        $authorize->update_user_setting($new_name, 'perm_thread_panel', TRUE);
        $authorize->update_user_setting($new_name, 'perm_mod_mode', TRUE);
        $authorize->update_user_setting($new_name, 'perm_ban', TRUE);
        $authorize->update_user_setting($new_name, 'perm_delete', TRUE);
        $authorize->update_user_setting($new_name, 'perm_post', TRUE);
        $authorize->update_user_setting($new_name, 'perm_post_anon', TRUE);
        $authorize->update_user_setting($new_name, 'perm_sticky', TRUE);
        $authorize->update_user_setting($new_name, 'perm_update_pages', FALSE);
        $authorize->update_user_setting($new_name, 'perm_update_cache', FALSE);
    }
    else if ($new_type === 'janitor')
    {
        $authorize->update_user_setting($new_name, 'perm_config', FALSE);
        $authorize->update_user_setting($new_name, 'perm_staff_panel', FALSE);
        $authorize->update_user_setting($new_name, 'perm_ban_panel', FALSE);
        $authorize->update_user_setting($new_name, 'perm_thread_panel', FALSE);
        $authorize->update_user_setting($new_name, 'perm_mod_mode', TRUE);
        $authorize->update_user_setting($new_name, 'perm_ban', FALSE);
        $authorize->update_user_setting($new_name, 'perm_delete', TRUE);
        $authorize->update_user_setting($new_name, 'perm_post', FALSE);
        $authorize->update_user_setting($new_name, 'perm_post_anon', FALSE);
        $authorize->update_user_setting($new_name, 'perm_sticky', FALSE);
        $authorize->update_user_setting($new_name, 'perm_update_pages', FALSE);
        $authorize->update_user_setting($new_name, 'perm_update_cache', FALSE);
    }
    else
    {
        nel_derp(151, array('origin' => 'ADMIN'));
    }

    $authorize->write_auth_file();
}
?>