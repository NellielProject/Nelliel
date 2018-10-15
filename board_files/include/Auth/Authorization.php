<?php

namespace Nelliel\Auth;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Authorization
{
    private $database;
    private $users = array();
    private $roles = array();

    function __construct($database)
    {
        $this->database = $database;
    }

    public function getUser($user_id)
    {
        if (isset($this->users[$user_id]))
        {
            return $this->users[$user_id];
        }

        $this->users[$user_id] = new AuthUser($this->database, $user_id);

        if ($this->users[$user_id]->loadFromDatabase())
        {
            return $this->users[$user_id];
        }

        return false;
    }

    public function userExists($user_id)
    {
        if ($this->getUser($user_id) !== false)
        {
            return true;
        }

        return false;
    }

    public function getRole($role_id)
    {
        if (isset($this->roles[$role_id]))
        {
            return $this->roles[$role_id];
        }

        $this->roles[$role_id] = new AuthRole($this->database, $role_id);

        if ($this->roles[$role_id]->loadFromDatabase())
        {
            return $this->roles[$role_id];
        }

        return false;
    }

    public function roleExists($role_id)
    {
        if ($this->getRole($role_id) !== false)
        {
            return true;
        }

        return false;
    }

    public function roleLevelCheck($role1, $role2, $false_if_equal = false)
    {
        if (!$this->roleExists($role1))
        {
            return false;
        }

        if (!$this->roleExists($role2))
        {
            return true;
        }

        $level1 = $this->roles[$role1]->auth_data['role_level'];
        $level2 = $this->roles[$role2]->auth_data['role_level'];

        if ($false_if_equal)
        {
            return $level1 > $level2;
        }
        else
        {
            return $level1 >= $level2;
        }
    }

    public function saveUsers()
    {
        foreach ($this->users as $user)
        {
            $user->writeToDatabase();
        }
    }

    public function saveRoles()
    {
        foreach ($this->roles as $role)
        {
            $role->writeToDatabase();
        }
    }
}
