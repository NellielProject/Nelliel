<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_get_authorization()
{
    static $authorized;

    if (!isset($authorized))
    {
        $authorized = new nel_authorization();
    }

    return $authorized;
}

//
// Handle all the authorization functions
//
class nel_authorization
{
    private $dbh;
    private $users = array();
    private $roles = array();
    private $users_modified = array();
    private $roles_modified = array();

    function __construct()
    {
        $this->dbh = nel_get_db_handle();
    }

    public function user_exists($user)
    {
        if ($this->user_loaded($user))
        {
            return true;
        }
        else
        {
            if ($this->set_up_user($user) !== false)
            {
                return true;
            }
        }

        return false;
    }

    public function role_exists($role)
    {
        if ($this->role_loaded($role))
        {
            return true;
        }
        else
        {
            if ($this->set_up_role($role) !== false)
            {
                return true;
            }
        }

        return false;
    }

    private function user_loaded($user)
    {
        return array_key_exists($user, $this->users);
    }

    private function role_loaded($role)
    {
        return array_key_exists($role, $this->roles);
    }

    private function load_all_users($retain)
    {
        $user_list = $this->get_user_list();

        foreach ($user_list as $user_id)
        {
            if (!$this->user_loaded($user) || !$retain)
            {
                $this->set_up_user($user_id);
            }
        }
    }

    private function load_all_roles($retain)
    {
        $role_list = $this->get_role_list();

        foreach ($role_list as $role_id)
        {
            if (!$this->role_loaded($role_id) || !$retain)
            {
                $this->set_up_role($role_id);
            }
        }
    }

    private function get_user_list()
    {
        $result = $this->dbh->query('SELECT user_id FROM "' . USER_TABLE . '";');
        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    private function get_role_list()
    {
        $result = $this->dbh->query('SELECT role_id FROM "' . ROLES_TABLE . '";');
        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    private function load_user($user)
    {
        $result = $this->dbh->query('SELECT * FROM "' . USER_TABLE . '" WHERE "user_id" = \'' . $user . '\';');

        if ($result !== false)
        {
            return $result->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    private function load_role($role)
    {
        $result = $this->dbh->query('SELECT * FROM "' . ROLES_TABLE . '" WHERE "role_id" = \'' . $role . '\';');

        if ($result !== false)
        {
            return $result->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    public function set_up_user($user)
    {
        $user_data = $this->load_user($user);

        if ($user_data === false)
        {
            return false;
        }

        foreach ($user_data as $key => $value)
        {
            $this->users[$user][$key] = $value;
        }

        if($this->users[$user]['role_id'] != '')
        {
            $this->set_up_role($this->users[$user]['role_id']);
        }

        return true;
    }

    public function set_up_role($role)
    {
        $role_data = $this->load_role($role);

        if ($role_data === false)
        {
            return false;
        }

        foreach ($role_data as $key => $value)
        {
            if (substr($key, 0, 5) === 'perm_')
            {
                $this->roles[$role][$key] = ($value) ? true : false;
            }
            else
            {
                $this->roles[$role][$key] = $value;
            }
        }

        return true;
    }

    public function get_user($user)
    {
        if ($this->user_exists($user))
        {
            return $this->users[$user];
        }

        return false;
    }

    public function get_user_info($user, $info)
    {
        if ($this->user_exists($user))
        {
            return $this->users[$user][$info];
        }

        return false;
    }

    public function get_role($role)
    {
        if ($this->role_exists($role))
        {
            return $this->roles[$role];
        }

        return false;
    }

    public function get_role_info($role, $info)
    {
        if ($this->role_exists($role))
        {
            return $this->roles[$role][$info];
        }

        return false;
    }

    public function get_role_perms($role)
    {
        $perms = array();

        foreach ($this->roles[$role] as $key => $value)
        {
            if (substr($key, 0, 5) === 'perm_')
            {
                $perms[$key] = $value;
            }
        }

        return $perms;
    }

    public function get_user_perms($user)
    {
        if($this->user_exists($user))
        {
            return $this->get_role_perms($this->users[$user]['role_id']);
        }
    }

    public function get_user_perm($user, $perm)
    {
        if($this->user_exists($user))
        {
            return $this->roles[$this->users[$user]['role_id']][$perm];
        }
    }

    public function update_user($user, $update)
    {
        if ($this->user_exists($user))
        {
            $this->users[$user] = $update;
            $this->users_modified[$user] = true;
            return true;
        }

        return false;
    }

    public function update_user_info($user, $info, $update)
    {
        if ($this->user_exists($user))
        {
            $this->users[$user][$info] = $update;
            $this->users_modified[$user] = true;
            return true;
        }

        return false;
    }

    public function update_role($role, $update)
    {
        if ($this->role_exists($role))
        {
            $this->roles[$role] = $update;
            $this->roles_modified[$role] = true;
            return true;
        }

        return false;
    }

    public function update_perm($role, $perm, $update)
    {
        if ($this->role_exists($role))
        {
            $this->roles[$role][$perm] = $update;
            $this->roles_modified[$role] = true;
            return true;
        }

        return false;
    }

    public function save_roles()
    {
        foreach($this->roles_modified as $key => $value)
        {
            $this->save_role($key);
        }
    }

    public function save_users()
    {
        foreach($this->users_modified as $key => $value)
        {
            $this->save_user($key);
        }
    }

    private function save_user($user)
    {
        $user_data = $this->users[$user];
        $update_user = '';

        foreach ($user_data as $key => $value)
        {
            $update_user .= '"' . $key . '" = :' . $key . ', ';
            $bind_user[':' . $key] = $value;
        }

        $prepared = $this->dbh->prepare('UPDATE "' . USER_TABLE . '" WHERE "user_id" = \'' . $user . '\' SET ' .
             $update_user . ';');

        foreach ($bind_user as $key => $value)
        {
            $prepared->bindValue($key, $value);
        }

        $prepared->execute();
    }

    private function save_role($role)
    {
        $role_data = $this->roles[$role];
        $update_role = '';

        foreach ($role_data as $key => $value)
        {
            $update_role .= '"' . $key . '" = :' . $key . ', ';
            $bind_role[':' . $key] = $value;
        }

        $prepared = $this->dbh->prepare('UPDATE "' . ROLES_TABLE . '" WHERE "role_id" = \'' . $role . '\' SET ' .
             $update_role . ';');

        foreach ($bind_role as $key => $value)
        {
            $prepared->bindValue($key, $value);
        }

        $prepared->execute();
    }
}
