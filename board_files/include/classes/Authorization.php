<?php

namespace Nelliel;

use \PDO;
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Handle all the authorization functions
//
class Authorization
{
    private $dbh;
    private $users = array();
    private $roles = array();
    private $user_roles = array();
    private $users_modified = array();
    private $roles_modified = array();
    private $user_roles_modified = array();

    function __construct()
    {
        $this->dbh = nel_database();
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
        $result = $this->dbh->query('SELECT "user_id" FROM "' . USER_TABLE . '"');
        return $result->fetch(FETCH_COLUMN);
    }

    private function get_role_list()
    {
        $result = $this->dbh->query('SELECT "role_id" FROM "' . ROLES_TABLE . '"');
        return $result->fetchAll(FETCH_COLUMN);
    }

    private function load_user($user_id)
    {
        $query = 'SELECT * FROM "' . USER_TABLE . '" WHERE "user_id" = ? LIMIT 1';
        $prepared = $this->dbh->prepare($query);
        $prepared->bindValue(1, $user_id, PDO::PARAM_STR);
        return $this->dbh->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC, true);
    }

    private function load_user_roles($user_id)
    {
        $query = 'SELECT * FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = ? LIMIT 1';
        $prepared = $this->dbh->prepare($query);
        $prepared->bindValue(1, $user_id, PDO::PARAM_STR);
        return $this->dbh->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
    }

    private function load_role($role_id)
    {
        $query = 'SELECT * FROM "' . ROLES_TABLE . '" WHERE "role_id" = ? LIMIT 1';
        $prepared = $this->dbh->prepare($query);
        $prepared->bindValue(1, $role_id, PDO::PARAM_STR);
        return $this->dbh->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC, true);
    }

    private function load_role_permissons($role_id)
    {
        $query = 'SELECT "perm_id", "perm_setting" FROM "' . 'nelliel_permissions' . '" WHERE "role_id" = ?';
        $prepared = $this->dbh->prepare($query);
        $prepared->bindValue(1, $role_id, PDO::PARAM_STR);
        return $this->dbh->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
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

        $user_roles = $this->load_user_roles($user);

        foreach ($user_roles as $role)
        {
            if ($role['board'] == null || $role['board'] == '')
            {
                $this->user_roles[$user][$role['role_id']] = null;
            }
            else
            {
                $this->user_roles[$user][$role['role_id']] = $role['board'];
            }
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
            $this->roles[$role][$key] = $value;
        }

        $this->set_up_role_permissions($role);

        return true;
    }

    public function set_up_role_permissions($role_id)
    {
        $perms = $this->load_role_permissons($role_id);

        if ($perms === false)
        {
            return false;
        }

        foreach ($perms as $perm)
        {
            $this->roles[$role_id]['permissions'][$perm['perm_id']] = $perm['perm_setting'] == 1 ? true : false;
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

    public function get_role_all_perms($role_id)
    {
        $perms = array();

        if ($this->role_exists($role_id))
        {
            $perms = $this->roles[$role_id]['permissions'];
        }

        return $perms;
    }

    public function get_role_perm($role_id, $perm)
    {
        if ($this->role_exists($role_id))
        {
            return isset($this->roles[$role_id]['permissions'][$perm]) && $this->roles[$role_id]['permissions'][$perm];
        }

        return false;
    }

    public function get_user_role($user, $board)
    {
        if ($this->user_exists($user))
        {
            return $this->user_roles[$user][$board];
        }

        return false;
    }

    public function get_user_perm($user, $perm, $board = null)
    {
        if ($this->user_exists($user))
        {
            foreach ($this->user_roles[$user] as $key => $value)
            {
                return $this->get_role_perm($key, $perm);
            }
        }

        return false;
    }

    public function get_tripcode_user($tripcode)
    {
        $query = 'SELECT "user_id" FROM "' . USER_TABLE . '" WHERE "user_tripcode" = ?';
        $prepared = $this->dbh->prepare($query);
        $prepared->bindValue(1, $tripcode, PDO::PARAM_STR);
        return $this->dbh->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN, true);
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
            $this->roles[$role]['permissions'][$perm] = $update;
            $this->roles_modified[$role] = true;
            return true;
        }

        return false;
    }

    public function role_level_check($role1, $role2, $false_if_equal = false)
    {
        if (!$this->role_exists($role1))
        {
            return false;
        }

        if (!$this->role_exists($role2))
        {
            return true;
        }

        $level1 = $this->get_role_info($role1, 'role_level');
        $level2 = $this->get_role_info($role2, 'role_level');

        if ($false_if_equal)
        {
            return $level1 > $level2;
        }
        else
        {
            return $level1 >= $level2;
        }
    }

    public function save_roles()
    {
        foreach ($this->roles_modified as $key => $value)
        {
            $this->save_role($key);
        }
    }

    public function save_users()
    {
        foreach ($this->users_modified as $key => $value)
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
            if ($key === 'permissions')
            {
                $this->save_permissions($role);
                continue;
            }

            $update_user .= '"' . $key . '" = :' . $key . ', ';
        }

        $update_role = substr($update_user, 0, -2);
        $query = 'UPDATE "' . USER_TABLE . '" SET ' . $update_user . ' WHERE "user_id" = :user';
        $prepared = $this->dbh->prepare($query);
        $prepared->bindValue(':user', $user, PDO::PARAM_STR);

        foreach ($user_data as $key => $value)
        {
            $prepared->bindValue(':' . $key, $value);
        }

        $this->dbh->executePrepared($prepared, null, true);
    }

    private function save_role($role)
    {
        $role_data = $this->roles[$role];
        $update_role = '';

        foreach ($role_data as $key => $value)
        {
            if ($key === 'permissions')
            {
                $this->save_permissions($role);
                continue;
            }

            $update_role .= '"' . $key . '" = :' . $key . ', ';
        }

        $update_role = substr($update_role, 0, -2);
        $query = 'UPDATE "' . ROLES_TABLE . '" SET ' . $update_role . ' WHERE "role_id" = :role';
        $prepared = $this->dbh->prepare($query);
        $prepared->bindValue(':role', $role, PDO::PARAM_STR);

        foreach ($role_data as $key => $value)
        {
            $prepared->bindValue(':' . $key, $value);
        }

        $this->dbh->executePrepared($prepared, null, true);
    }

    private function save_permissions($role)
    {
        $perms_data = $this->roles[$role]['permissions'];

        foreach ($perms_data as $key => $value)
        {
            $query = 'UPDATE "' . PERMISSIONS_TABLE . '" SET "perm_setting" = :setting WHERE "perm_id" = \'' . $key .
                 '\' AND "role_id" = :role';
            $prepared = $this->dbh->prepare($query);
            $prepared->bindValue(':setting', $value, PDO::PARAM_INT);
            $prepared->bindValue(':role', $role, PDO::PARAM_INT);
            $this->dbh->executePrepared($prepared, null, true);
        }
    }
}
