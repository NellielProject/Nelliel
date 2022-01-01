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
        $new_user = new AuthUser($this->database, $username, $db_load);
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
        return $this->userLoaded($username) || $this->newUser($username, false, true)->loadFromDatabase();
    }

    public function userLoaded(string $username): bool
    {
        $username_lower = utf8_strtolower($username);
        return isset(self::$users[$username_lower]);
    }

    public function getUser(string $username): AuthUser
    {
        $username_lower = utf8_strtolower($username);

        if ($this->userExists($username)) {
            if (!$this->userLoaded($username)) {
                $this->newUser($username);
            }

            return self::$users[$username_lower];
        } else {
            return $this->emptyUser();
        }
    }

    public function removeUser(string $username): bool
    {
        $username_lower = utf8_strtolower($username);

        if (!$this->userExists($username)) {
            return false;
        }

        $user = $this->getUser($username);
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
        self::$roles[$role_id] = new AuthRole($this->database, $role_id);
        self::$roles[$role_id]->setupNew();
        return self::$roles[$role_id];
    }

    public function roleExists(string $role_id): bool
    {
        if ($this->getRole($role_id) !== false) {
            return true;
        }

        return false;
    }

    public function getRole(string $role_id): AuthRole
    {
        if (isset(self::$roles[$role_id])) {
            return self::$roles[$role_id];
        }

        self::$roles[$role_id] = new AuthRole($this->database, $role_id);

        if (self::$roles[$role_id]->loadFromDatabase()) {
            return self::$roles[$role_id];
        }

        return $this->emptyRole();
    }

    public function removeRole(string $role_id): bool
    {
        if (!isset(self::$roles[$role_id])) {
            return false;
        }

        self::$roles[$role_id]->remove();
        unset(self::$roles[$role_id]);
        return true;
    }

    public function roleLevelCheck(string $role1, string $role2, bool $false_if_equal = false): bool
    {
        if (!$this->roleExists($role1)) {
            return false;
        }

        if (!$this->roleExists($role2)) {
            return true;
        }

        $level1 = self::$roles[$role1]->getData('role_level');
        $level2 = self::$roles[$role2]->getData('role_level');

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
