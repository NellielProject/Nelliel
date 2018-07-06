<?php

namespace Nelliel;

use PDO;
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
        $query = 'SELECT * FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = ?';
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

    public function set_up_user($user_id)
    {
        $user_data = $this->load_user($user_id);

        if ($user_data === false)
        {
            return false;
        }

        foreach ($user_data as $key => $value)
        {
            $this->users[$user_id][$key] = $value;
        }

        $user_roles = $this->load_user_roles($user_id);

        foreach ($user_roles as $role)
        {
            foreach ($role as $key => $value)
            {
                $this->user_roles[$user_id][$role['board']][$key] = $value;
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

    public function get_user_role($user, $board_id = '')
    {
        if ($this->user_exists($user))
        {
            if (isset($this->user_roles[$user]['']))
            {
                return $this->user_roles[$user][''];
            }

            if (isset($this->user_roles[$user][$board_id]))
            {
                return $this->user_roles[$user][$board_id];
            }
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

    public function get_user_perm($user_id, $perm, $board = '')
    {
        if ($this->user_exists($user_id))
        {
            if (isset($this->user_roles[$user_id][$board]) &&
                $this->get_role_perm($this->user_roles[$user_id][$board]['role_id'], $perm))
            {
                return true;
            }

            if (isset($this->user_roles[$user_id]['']) &&
                $this->get_role_perm($this->user_roles[$user_id]['']['role_id'], $perm))
            {
                return true;
            }
        }

        return false;
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

    public function update_user_info($user_id, $info, $update)
    {
        if ($this->user_exists($user_id))
        {
            $this->users[$user_id][$info] = $update;
            $this->users_modified[$user_id] = true;
            return true;
        }

        return false;
    }

    public function update_role($role_id, $update)
    {
        if ($this->role_exists($role_id))
        {
            $this->roles[$role_id] = $update;
            $this->roles_modified[$role_id] = true;
            return true;
        }

        return false;
    }

    public function update_user_role($user_id, $update, $board_id, $remove = false)
    {
        if ($this->user_exists($user_id))
        {
            $updated = false;

            if ($update['all_boards'] == 1)
            {
                $update['board'] = '';
            }

            foreach ($update as $key => $value)
            {
                $this->user_roles[$user_id][$board_id][$key] = $value;
            }

            $this->user_roles[$user_id][$board_id]['remove'] = $remove;
            $this->user_roles_modified[$user_id] = true;
            return true;
        }

        return false;
    }

    public function update_perm($role_id, $perm, $update)
    {
        if ($this->role_exists($role_id))
        {
            $this->roles[$role_id]['permissions'][$perm] = $update;
            $this->roles_modified[$role_id] = true;
            return true;
        }

        return false;
    }

    public function user_highest_level_role($user_id, $board_id = null) // TODO: User exists checks here
    {
        $role_level = 0;
        $role = '';

        foreach ($this->user_roles[$user_id] as $key => $value)
        {
            $level = $this->get_role_info($value['role_id'], 'role_level');

            if ($key !== '' && $board_id !== $key)
            {
                $level = 0;
            }

            if ($level > $role_level)
            {
                $role_level = $this->get_role_info($value['role_id'], 'role_level');
                $role = $value['role_id'];
            }
        }

        return $role;
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
            $this->roles_modified[$key] = false;
        }
    }

    public function save_users()
    {
        foreach ($this->users_modified as $key => $value)
        {
            $this->save_user($key);
            $this->users_modified[$key] = false;
        }
    }

    public function save_user_roles()
    {
        foreach ($this->user_roles_modified as $key => $value)
        {
            $this->save_user_role($key);
            $this->user_roles_modified[$key] = false;
        }
    }

    private function save_user_role($user_id)
    {
        $user_role_data = $this->user_roles[$user_id];

        foreach ($user_role_data as $board => $set)
        {
            $prepared = $this->dbh->prepare('SELECT "entry" FROM "' . USER_ROLE_TABLE .
                '" WHERE "user_id" = ? AND "board" = ? LIMIT 1');
            $entry = $this->dbh->executePreparedFetch($prepared, array($user_id, $set['board']), PDO::FETCH_COLUMN, true);

            if ($entry !== false)
            {
                if (isset($set['remove']) && $set['remove'] === true)
                {
                    $prepared = $this->dbh->prepare('DELETE FROM "' . USER_ROLE_TABLE . '" WHERE "entry" = ?');
                    $this->dbh->executePrepared($prepared, array($entry));
                    unset($this->user_roles[$user_id][$board]);
                }
                else
                {
                    $prepared = $this->dbh->prepare('UPDATE "' . USER_ROLE_TABLE .
                        '" SET "role_id" = ?, "board" = ?, "all_boards" = ? WHERE "entry" = ?');
                    $this->dbh->executePrepared($prepared, array($set['role_id'], $set['board'], $set['all_boards'],
                        $entry));
                }
            }
            else
            {
                $prepared = $this->dbh->prepare('INSERT INTO "' . USER_ROLE_TABLE .
                    '" ("user_id", "role_id", "board", "all_boards") VALUES (?, ?, ?, ?)');
                $this->dbh->executePrepared($prepared, array($user_id, $set['role_id'], $set['board'],
                    $set['all_boards']));
            }
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

        $update_user = substr($update_user, 0, -2);
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
            if ($key === 'permissions')
            {
                continue;
            }

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
