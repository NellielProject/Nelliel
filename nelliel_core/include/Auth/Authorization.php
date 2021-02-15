<?php

declare(strict_types=1);

namespace Nelliel\Auth;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\NellielPDO;

class Authorization
{
    private $database;
    private static $users = array();
    private static $roles = array();

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function newUser(string $user_id, bool $db_load = true, bool $temp = false)
    {
        $user_id_lower = utf8_strtolower($user_id);
        $new_user = new AuthUser($this->database, $user_id, $db_load);
        $new_user->setupNew();

        if (!$temp)
        {
            self::$users[$user_id_lower] = $new_user;
        }

        return $new_user;
    }

    public function emptyUser()
    {
        return new AuthUser($this->database, '');
    }

    public function userExists(string $user_id)
    {
        $user_id_lower = utf8_strtolower($user_id);
        return isset(self::$users[$user_id_lower]) || $this->newUser($user_id, false, true)->loadFromDatabase();
    }

    public function userLoaded(string $user_id)
    {
        $user_id_lower = utf8_strtolower($user_id);
        return isset(self::$users[$user_id_lower]);
    }

    public function getUser(string $user_id)
    {
        $user_id_lower = utf8_strtolower($user_id);

        if ($this->userExists($user_id))
        {
            if (!$this->userLoaded($user_id))
            {
                $this->newUser($user_id);
            }

            return self::$users[$user_id_lower];
        }
        else
        {
            return $this->emptyUser();
        }
    }

    public function removeUser(string $user_id)
    {
        $user_id_lower = utf8_strtolower($user_id);

        if (!$this->userExists($user_id))
        {
            return false;
        }

        $user = $this->getUser($user_id);
        $user->remove();
        unset(self::$users[$user_id_lower]);
        return true;
    }

    public function isSiteOwner(string $user_id)
    {
        $user_id_lower = utf8_strtolower($user_id);
        return self::$users[$user_id_lower]->isSiteOwner();
    }

    public function newRole(string $role_id)
    {
        self::$roles[$role_id] = new AuthRole($this->database, $role_id);
        self::$roles[$role_id]->setupNew();
        return self::$roles[$role_id];
    }

    public function roleExists(string $role_id)
    {
        if ($this->getRole($role_id) !== false)
        {
            return true;
        }

        return false;
    }

    public function getRole(string $role_id)
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

    public function removeRole(string $role_id)
    {
        if (!isset(self::$roles[$role_id]))
        {
            return false;
        }

        self::$roles[$role_id]->remove();
        unset(self::$roles[$role_id]);
        return true;
    }

    public function roleLevelCheck(string $role1, string $role2, bool $false_if_equal = false)
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
