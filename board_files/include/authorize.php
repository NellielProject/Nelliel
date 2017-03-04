<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Handle all the authorization functions
//
class nel_authorization
{
    private $authorized = array();

    function __construct()
    {
        // We need a check if this exists, etc.
        include BOARD_FILES . 'auth_data.nel.php';
        $this->authorized = $authorized;
    }

    private function key_exists($key)
    {
        return array_key_exists($key, $this->authorized);
    }

    public function is_authorized($user, $setting)
    {
        if (is_boolean($this->authorized[$user][$setting]))
        {
            return $this->authorized[$user][$setting];
        }

        return FALSE;
    }

    public function get_user_auth($user)
    {
        if ($this->key_exists($user))
        {
            return $this->authorized[$user];
        }

        return FALSE;
    }

    public function get_user_setting($user, $setting)
    {
        if ($this->key_exists($user))
        {
            return $this->authorized[$user]['settings'][$setting];
        }

        return FALSE;
    }

    public function get_user_perm($user, $perm)
    {
        if ($this->key_exists($user))
        {
            return $this->authorized[$user]['perms'][$perm];
        }

        return FALSE;
    }

    public function update_user_auth($user, $update)
    {
        $this->authorized[$user] = $update;
    }

    public function update_user_setting($user, $setting, $update)
    {
        return $this->authorized[$user]['settings'][$setting] = $update;
    }

    public function update_user_perm($user, $perm, $update)
    {
        return $this->authorized[$user]['perms'][$perm] = $update;
    }

    public function remove_user_auth($user)
    {
        if ($this->key_exists($user))
        {
            unset($this->authorized[$user]);
        }
    }

    public function get_blank_settings()
    {
        return array('settings' => array('staff_password' => '', 'staff_type' => '', 'staff_trip' => ''), 'perms' => array('perm_config' => FALSE, 'perm_staff_panel' => FALSE, 'perm_ban_panel' => FALSE, 'perm_thread_panel' => FALSE, 'perm_mod_mode' => FALSE, 'perm_ban' => FALSE, 'perm_delete' => FALSE, 'perm_post' => FALSE, 'perm_post_anon' => FALSE, 'perm_sticky' => FALSE, 'perm_update_pages' => FALSE, 'perm_update_cache' => FALSE));
    }

    public function write_auth_file()
    {
        $new_auth = '<?php $authorized = ' . var_export($this->authorized) . '?>';
        nel_write_file(FILES_PATH . '/auth_data.nel.php', $new_auth, 0644);
    }
}

?>
