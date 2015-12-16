<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_authorization($user, $setting, $is_update, $new_data)
{
    static $authorized;

    if (!isset($authorized))
    {
        include BOARD_FILES . 'auth_data.nel.php';
    }

    if(!is_null($user) && is_null($setting))
    {
        if($is_update)
        {
            if(is_null($new_data))
            {
                unset($authorized[$user]);
            }
            else
            {
                $authorized[$user] = $new_data;
            }
        }
        else
        {
            if(!isset($authorized[$user]))
            {
                return FALSE;
            }

            return $authorized[$user];
        }
    }
    else if(!is_null($user) && !is_null($setting))
    {
        if($is_update)
        {
            $authorized[$user][$setting] = $new_data;
        }
        else
        {
            if(!isset($authorized[$user]) || !isset($authorized[$user][$setting]))
            {
                return FALSE;
            }

            return $authorized[$user][$setting];
        }
    }
    else
    {
        return $authorized;
    }
}

function get_user_auth($user)
{
    return nel_authorization($user, NULL, FALSE, NULL);
}

function get_user_setting($user, $setting)
{
    return nel_authorization($user, $setting, FALSE, NULL);
}

function update_user_auth($user, $update)
{
    nel_authorization($user, NULL, TRUE, $update);
}

function update_user_setting($user, $setting, $update)
{
    nel_authorization($user, $setting, TRUE, $update);
}

function is_authorized($user, $perm)
{
    return nel_authorization($user, $perm, FALSE, NULL);
}

function remove_user_auth($user)
{
    nel_authorization($user, NULL, TRUE, NULL);
}

function get_blank_settings()
{
    return array('staff_password' => '',
		'staff_type' => '',
		'staff_trip' => '',
		'perm_config' => FALSE,
		'perm_staff_panel' => FALSE,
		'perm_ban_panel' => FALSE,
		'perm_thread_panel' => FALSE,
		'perm_mod_mode' => FALSE,
		'perm_ban' => FALSE,
		'perm_delete' => FALSE,
		'perm_post' => FALSE,
		'perm_post_anon' => FALSE,
		'perm_sticky' => FALSE,
		'perm_update_pages' => FALSE,
		'perm_update_cache' => FALSE);
}

function write_auth_file()
{
    $new_auth = '<?php $authorized = ' . var_export(nel_authorization(NULL, NULL, NULL, NULL), TRUE) . '?>';
    write_file(FILES_PATH . '/auth_data.nel.php', $new_auth, 0644);
}

?>