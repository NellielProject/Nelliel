<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Authorization
{
    private $dbh;
    private $users = array();
    private $roles = array();
    private $user_roles = array();

    function __construct()
    {
        $this->dbh = nel_database();
    }

    public function userExists($user)
    {
        if ($this->userLoaded($user))
        {
            return true;
        }
        else
        {
            if ($this->setupUser($user) !== false)
            {
                return true;
            }
        }

        return false;
    }

    public function roleExists($role)
    {
        if ($this->roleLoaded($role))
        {
            return true;
        }
        else
        {
            if ($this->setupRole($role) !== false)
            {
                return true;
            }
        }

        return false;
    }

    private function userLoaded($user)
    {
        return array_key_exists($user, $this->users);
    }

    private function roleLoaded($role)
    {
        return array_key_exists($role, $this->roles);
    }

    private function loadUser($user_id)
    {
        $query = 'SELECT * FROM "' . USER_TABLE . '" WHERE "user_id" = ? LIMIT 1';
        $prepared = $this->dbh->prepare($query);
        $prepared->bindValue(1, $user_id, PDO::PARAM_STR);
        return $this->dbh->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC, true);
    }

    private function loadUserRoles($user_id)
    {
        $query = 'SELECT * FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = ?';
        $prepared = $this->dbh->prepare($query);
        $prepared->bindValue(1, $user_id, PDO::PARAM_STR);
        return $this->dbh->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
    }

    private function loadRole($role_id)
    {
        $query = 'SELECT * FROM "' . ROLES_TABLE . '" WHERE "role_id" = ? LIMIT 1';
        $prepared = $this->dbh->prepare($query);
        $prepared->bindValue(1, $role_id, PDO::PARAM_STR);
        return $this->dbh->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC, true);
    }

    private function loadRolePermissons($role_id)
    {
        $query = 'SELECT "perm_id", "perm_setting" FROM "' . 'nelliel_permissions' . '" WHERE "role_id" = ?';
        $prepared = $this->dbh->prepare($query);
        $prepared->bindValue(1, $role_id, PDO::PARAM_STR);
        return $this->dbh->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
    }

    public function setupUser($user_id)
    {
        $user_info = $this->loadUser($user_id);

        if ($user_info === false)
        {
            return false;
        }

        $this->users[$user_id] = $user_info;
        $user_roles = $this->loadUserRoles($user_id);

        if ($user_roles === false)
        {
            return false;
        }

        $this->user_roles[$user_id] = $user_roles;

        foreach ($user_roles as $role)
        {
            $this->setupRole($role['role_id']);
        }

        return true;
    }

    public function setupRole($role)
    {
        $role_data = $this->loadRole($role);

        if ($role_data === false)
        {
            return false;
        }

        foreach ($role_data as $key => $value)
        {
            $this->roles[$role][$key] = $value;
        }

        $this->setupRolePermissions($role);

        return true;
    }

    public function setupRolePermissions($role_id)
    {
        $perms = $this->loadRolePermissons($role_id);

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

    public function getUser($user_id)
    {
        if (!$this->userExists($user_id))
        {
            return false;
        }

        return $this->users[$user_id];
    }

    public function getUserInfo($user_id, $info)
    {
        if (!$this->userExists($user_id))
        {
            return false;
        }

        return $this->users[$user_id][$info];
    }

    public function updateUserInfo($user_id, $info, $update)
    {
        if (!$this->userExists($user_id))
        {
            return false;
        }

        $this->users[$user_id][$info] = $update;
        $this->users[$user_id]['modified'] = true;
        return true;
    }

    public function getUserBoardRole($user_id, $board_id)
    {
        if (!$this->userExists($user_id))
        {
            return false;
        }

        foreach ($this->user_roles[$user_id] as $role)
        {
            if ($role['board'] === $board_id)
            {
                return $role['role_id'];
            }
        }

        return false;
    }

    public function getUserPerm($user_id, $perm, $board_id = null)
    {
        if (!$this->userExists($user_id))
        {
            return false;
        }

        foreach ($this->user_roles[$user_id] as $role)
        {
            if (!is_null($board_id) && $role['board'] !== $board_id && $role['board'] !== '')
            {
                continue;
            }

            $perm_setting = $this->getRolePerm($role['role_id'], $perm);

            if ($perm_setting !== false)
            {
                return $perm_setting;
            }
        }

        return false;
    }

    public function getRole($role_id)
    {
        if (!$this->roleExists($role_id))
        {
            return false;
        }

        return $this->roles[$role_id];
    }

    public function getRoleInfo($role_id, $info)
    {
        if (!$this->roleExists($role_id))
        {
            return false;
        }

        return $this->roles[$role_id][$info];
    }


    public function updateRoleInfo($role_id, $info, $update)
    {
        if (!$this->roleExists($role_id))
        {
            return false;
        }

        $this->roles[$role_id][$info] = $update;
        $this->roles[$role_id]['modified'] = true;
        return true;
    }

    public function getRolePerm($role_id, $perm)
    {
        if (!$this->roleExists($role_id))
        {
            return false;
        }

        return $this->roles[$role_id]['permissions'][$perm];
    }

    public function updateRolePerm($role_id, $perm, $update)
    {
        if (!$this->roleExists($role_id))
        {
            return false;
        }

        $this->roles[$role_id]['permissions'][$perm] = $update;
        $this->roles[$role_id]['modified'] = true;
        return true;
    }

    public function updateUserRole($user_id, $role_id, $board_id, $remove = false)
    {
        if (!$this->userExists($user_id))
        {
            return false;
        }

        foreach ($this->user_roles[$user_id] as $key => $user_role)
        {
            if ($user_role['board'] === $board_id)
            {
                if ($remove)
                {
                    $this->user_roles[$user_id][$key]['remove'] = true;
                }
                else
                {
                    $this->user_roles[$user_id][$key]['board'] = $board_id;
                    $this->user_roles[$user_id][$key]['role_id'] = $role_id;
                }

                $this->user_roles[$user_id][$key]['modified'] = true;
                return true;
            }
        }

        if (!$remove)
        {
            $this->user_roles[$user_id][$key]['user_id'] = $user_id;
            $this->user_roles[$user_id][$key]['role_id'] = $role_id;
            $this->user_roles[$user_id][$key]['board'] = $board_id;
            $this->user_roles[$user_id][$key]['all_boards'] = ($board_id === '') ? 1 : 0;
            $this->user_roles[$user_id][$key]['modified'] = true;
            return true;
        }

        return false;
    }

    public function updatePerm($role_id, $perm, $update)
    {
        if ($this->roleExists($role_id))
        {
            $this->roles[$role_id]['permissions'][$perm] = $update;
            $this->roles_modified[$role_id] = true;
            return true;
        }

        return false;
    }

    public function userHighestLevelRole($user_id, $board_id = '')
    {
        $role = '';

        if (!$this->userExists($user_id))
        {
            return $role;
        }

        $role_level = 0;

        foreach ($this->user_roles[$user_id] as $role)
        {
            $level = $this->getRoleInfo($role['role_id'], 'role_level');

            if ($board_id !== '' && $board_id !== $role['board'])
            {
                $level = 0;
            }

            if ($level > $role_level)
            {
                $role_level = $this->getRoleInfo($role['role_id'], 'role_level');
                $role = $role['role_id'];
            }
        }

        return $role;
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

        $level1 = $this->getRoleInfo($role1, 'role_level');
        $level2 = $this->getRoleInfo($role2, 'role_level');

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
        foreach ($this->users as $user_id => $user_data)
        {
            if (!isset($user_data['modified']) || $user_data['modified'] !== true)
            {
                continue;
            }

            $update_user = '';

            foreach ($user_data as $key => $value)
            {
                if ($key === 'modified')
                {
                    continue;
                }

                $update_user .= '"' . $key . '" = :' . $key . ', ';
            }

            $update_user = substr($update_user, 0, -2);
            $query = 'UPDATE "' . USER_TABLE . '" SET ' . $update_user . ' WHERE "user_id" = :user';
            $prepared = $this->dbh->prepare($query);
            $prepared->bindValue(':user', $user_id, PDO::PARAM_STR);

            foreach ($user_data as $key => $value)
            {
                if ($key === 'modified')
                {
                    continue;
                }

                $prepared->bindValue(':' . $key, $value);
            }

            $this->dbh->executePrepared($prepared, null, true);
            $this->users[$user_id]['modified'] = false;
        }
    }

    public function saveRoles()
    {
        foreach ($this->roles as $role => $role_data)
        {
            if (!isset($role_data['modified']) || $role_data['modified'] !== true)
            {
                continue;
            }

            $update_role = '';

            foreach ($role_data as $key => $value)
            {
                $this->savePermissions($role);

                if ($key === 'permissions' || $key === 'modified')
                {
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
                if ($key === 'permissions' || $key === 'modified')
                {
                    continue;
                }

                $prepared->bindValue(':' . $key, $value);
            }

            $this->dbh->executePrepared($prepared, null, true);
            $this->roles[$role]['modified'] = false;
        }
    }

    public function saveUserRoles()
    {
        foreach ($this->user_roles as $user_id => $user_roles)
        {
            foreach ($user_roles as $user_role)
            {
                if (!isset($user_role['modified']) || $user_role['modified'] !== true)
                {
                    continue;
                }

                $prepared = $this->dbh->prepare(
                        'SELECT "entry" FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = ? AND "board" = ? LIMIT 1');
                $entry = $this->dbh->executePreparedFetch($prepared, array($user_id, $user_role['board']),
                        PDO::FETCH_COLUMN, true);

                if ($entry !== false)
                {
                    if (isset($user_role['remove']) && $user_role['remove'] === true)
                    {
                        $prepared = $this->dbh->prepare('DELETE FROM "' . USER_ROLE_TABLE . '" WHERE "entry" = ?');
                        $this->dbh->executePrepared($prepared, array($entry));
                        unset($this->user_roles[$user_id][$board]);
                    }
                    else
                    {
                        $prepared = $this->dbh->prepare(
                                'UPDATE "' . USER_ROLE_TABLE . '" SET "role_id" = ?, "board" = ? WHERE "entry" = ?');
                        $this->dbh->executePrepared($prepared, array($user_role['role_id'], $user_role['board'], $entry));
                    }
                }
                else
                {
                    $prepared = $this->dbh->prepare(
                            'INSERT INTO "' . USER_ROLE_TABLE . '" ("user_id", "role_id", "board") VALUES (?, ?, ?, ?)');
                    $this->dbh->executePrepared($prepared, array($user_id, $user_role['role_id'], $user_role['board']));
                }
            }
        }
    }

    public function savePermissions($role)
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
