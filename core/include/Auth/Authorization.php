<?php
declare(strict_types = 1);

namespace Nelliel\Auth;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;

class Authorization
{
    private $database;
    private static $users = array();
    private static $roles = array();

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    private function newUser(string $username, bool $db_load = true): AuthUser
    {
        $username_lower = utf8_strtolower($username);
        $new_user = new AuthUser($this->database, $username_lower, $db_load);
        return $new_user;
    }

    public function emptyUser(): AuthUser
    {
        return new AuthUser($this->database, '', false);
    }

    public function userExists(string $username): bool
    {
        $username_lower = utf8_strtolower($username);
        return $this->getUser($username_lower)->exists();
    }

    public function getUser(string $username): AuthUser
    {
        $username_lower = utf8_strtolower($username);

        if (isset(self::$users[$username_lower])) {
            return self::$users[$username_lower];
        }

        $new_user = $this->newUser($username_lower);

        if (!$new_user->empty()) {
            self::$users[$username_lower] = $new_user;
        }

        return $new_user;
    }

    public function removeUser(string $username): void
    {
        $username_lower = utf8_strtolower($username);
        $user = $this->getUser($username_lower);
        $user->remove();
        unset(self::$users[$username_lower]);
    }

    public function emptyRole(): AuthRole
    {
        return new AuthRole($this->database, '', false);
    }

    private function newRole(string $role_id, bool $db_load = true): AuthRole
    {
        $role_lower = utf8_strtolower($role_id);
        $new_role = new AuthRole($this->database, $role_lower, $db_load);
        return $new_role;
    }

    public function roleExists(string $role_id): bool
    {
        $role_lower = utf8_strtolower($role_id);
        return $this->getRole($role_lower)->exists();
    }

    public function getRole(string $role_id): AuthRole
    {
        $role_lower = utf8_strtolower($role_id);

        if (isset(self::$roles[$role_lower])) {
            return self::$roles[$role_lower];
        }

        $new_role = $this->newRole($role_lower);

        if (!$new_role->empty()) {
            self::$roles[$role_lower] = $new_role;
        }

        return $new_role;
    }

    public function removeRole(string $role_id): void
    {
        $role_lower = utf8_strtolower($role_id);
        $role = $this->getRole($role_lower);
        $role->remove();
        unset(self::$roles[$role_lower]);
    }

    public function roleLevelCheck(string $role1, string $role2, bool $false_if_equal = false): bool
    {
        $role1_lower = utf8_strtolower($role1);
        $role2_lower = utf8_strtolower($role2);

        if (!$this->roleExists($role1_lower) || !$this->roleExists($role2_lower)) {
            return false;
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
