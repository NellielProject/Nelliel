<?php
declare(strict_types = 1);

namespace Nelliel\Auth;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

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

    public function newUser(string $username, bool $db_load = true, bool $temp = false): AuthUser
    {
        $username_lower = utf8_strtolower($username);
        $new_user = new AuthUser($this->database, $username_lower, $db_load);
        $new_user->setupNew();

        if (!$temp) {
            self::$users[$username_lower] = $new_user;
        }

        return $new_user;
    }

    public function emptyUser(): AuthUser
    {
        return new AuthUser($this->database, '');
    }

    public function userExists(string $username): bool
    {
        $username_lower = utf8_strtolower($username);
        return $this->userLoaded($username_lower) || $this->newUser($username_lower, false, true)->loadFromDatabase();
    }

    public function userLoaded(string $username): bool
    {
        $username_lower = utf8_strtolower($username);
        return isset(self::$users[$username_lower]);
    }

    public function getUser(string $username): AuthUser
    {
        $username_lower = utf8_strtolower($username);

        if ($this->userExists($username_lower)) {
            if (!$this->userLoaded($username_lower)) {
                $this->newUser($username_lower);
            }

            return self::$users[$username_lower];
        } else {
            return $this->emptyUser();
        }
    }

    public function removeUser(string $username): bool
    {
        $username_lower = utf8_strtolower($username);

        if (!$this->userExists($username_lower)) {
            return false;
        }

        $user = $this->getUser($username_lower);
        $user->remove();
        unset(self::$users[$username_lower]);
        return true;
    }

    public function isSiteOwner(string $username): bool
    {
        $username_lower = utf8_strtolower($username);
        return self::$users[$username_lower]->isSiteOwner();
    }

    public function emptyRole(): AuthRole
    {
        return new AuthRole($this->database, '');
    }

    public function newRole(string $role_id): AuthRole
    {
        $role_lower = utf8_strtolower($role_id);
        self::$roles[$role_lower] = new AuthRole($this->database, $role_lower);
        self::$roles[$role_lower]->setupNew();
        return self::$roles[$role_lower];
    }

    public function roleExists(string $role_id): bool
    {
        $role_lower = utf8_strtolower($role_id);

        if ($this->getRole($role_lower) !== false) {
            return true;
        }

        return false;
    }

    public function getRole(string $role_id): AuthRole
    {
        $role_lower = utf8_strtolower($role_id);

        if (isset(self::$roles[$role_lower])) {
            return self::$roles[$role_lower];
        }

        self::$roles[$role_lower] = new AuthRole($this->database, $role_lower);

        if (self::$roles[$role_lower]->loadFromDatabase()) {
            return self::$roles[$role_lower];
        }

        return $this->emptyRole();
    }

    public function removeRole(string $role_id): bool
    {
        $role_lower = utf8_strtolower($role_id);

        if (!isset(self::$roles[$role_lower])) {
            return false;
        }

        self::$roles[$role_lower]->remove();
        unset(self::$roles[$role_lower]);
        return true;
    }

    public function roleLevelCheck(string $role1, string $role2, bool $false_if_equal = false): bool
    {
        $role1_lower = utf8_strtolower($role1);
        $role2_lower = utf8_strtolower($role2);

        if (!$this->roleExists($role1_lower)) {
            return false;
        }

        if (!$this->roleExists($role2_lower)) {
            return true;
        }

        $level1 = self::$roles[$role1_lower]->getData('role_level');
        $level2 = self::$roles[$role2_lower]->getData('role_level');

        if ($false_if_equal) {
            return $level1 > $level2;
        } else {
            return $level1 >= $level2;
        }
    }

    public function saveUsers(): void
    {
        foreach (self::$users as $user) {
            if ($user->changed()) {
                $user->writeToDatabase();
            }
        }
    }

    public function saveRoles(): void
    {
        foreach (self::$roles as $role) {
            if ($role->changed()) {
                $role->writeToDatabase();
            }
        }
    }
}
