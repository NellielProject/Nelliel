<?php

namespace Nelliel\Auth;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Authorization
{
    private $database;
    private static $users = array();
    private static $roles = array();

    function __construct($database)
    {
        $this->database = $database;
    }

    public function newUser($user_id)
    {
        self::$users[$user_id] = new AuthUser($this->database, $user_id);
        self::$users[$user_id]->setupNew();
        return self::$users[$user_id];
    }

    public function getUser($user_id)
    {
        if (isset(self::$users[$user_id]))
        {
            return self::$users[$user_id];
        }

        self::$users[$user_id] = new AuthUser($this->database, $user_id);

        if (self::$users[$user_id]->loadFromDatabase())
        {
            return self::$users[$user_id];
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

    public function newRole($role_id)
    {
        self::$roles[$role_id] = new AuthRole($this->database, $role_id);
        self::$roles[$role_id]->setupNew();
        return self::$roles[$role_id];
    }

    public function getRole($role_id)
    {
        if (isset(self::$roles[$role_id]))
        {
            return self::$roles[$role_id];
        }

        self::$roles[$role_id] = new AuthRole($this->database, $role_id);

        if (self::$roles[$role_id]->loadFromDatabase())
        {
            return self::$roles[$role_id];
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

        $level1 = self::$roles[$role1]->auth_data['role_level'];
        $level2 = self::$roles[$role2]->auth_data['role_level'];

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
        foreach (self::$users as $user)
        {
            $user->writeToDatabase();
        }
    }

    public function saveRoles()
    {
        foreach (self::$roles as $role)
        {
            $role->writeToDatabase();
        }
    }
}
