<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class AuthUser extends AuthBase
{
    public $user_roles = array();

    function __construct($database, $user_id)
    {
        $this->database = $database;
        $this->auth_id = $user_id;
    }

    public function loadFromDatabase($temp_database = null)
    {
        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare('SELECT * FROM "' . USER_TABLE . '" WHERE "user_id" = ? LIMIT 1');
        $result = $database->executePreparedFetch($prepared, [$this->auth_id], PDO::FETCH_ASSOC, true);

        if (empty($result))
        {
            return false;
        }

        $this->auth_data = $result;

        $prepared = $database->prepare('SELECT * FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = ?');
        $result = $database->executePreparedFetchAll($prepared, [$this->auth_id], PDO::FETCH_ASSOC, true);

        if (empty($result))
        {
            return false;
        }

        foreach ($result as $row)
        {
            if ($row['role_id'] === '')
            {
                continue;
            }

            $user_role = new \Nelliel\AuthUserRole($database, $row['user_id'], $row['role_id'], $row['board']);
            $user_role->loadFromDatabase();
            $role = new \Nelliel\AuthRole($database, $row['role_id']);
            $role->loadFromDatabase();
            $this->user_roles[] = $user_role;
        }

        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (empty($this->auth_data))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare('SELECT "entry" FROM "' . USER_TABLE . '" WHERE "user_id" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->auth_id], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $database->prepare(
                    'UPDATE "' . USER_TABLE .
                    '" SET "user_id" = :user_id, "display_name" = :display_name, "user_password" = :user_password, "active" = :active, "failed_logins" = :failed_logins, "last_failed_login" = :last_failed_login WHERE "entry" = :entry');
            $prepared->bindValue(':entry', $result, PDO::PARAM_INT);
        }
        else
        {
            $prepared = $database->prepare(
                    'INSERT INTO "' . USER_TABLE . '" ("user_id", "display_name", "user_password", "active", "failed_logins", "last_failed_login") VALUES
                    (:user_id, :display_name, :user_password, :active, :failed_logins, :last_failed_login)');
        }

        $prepared->bindValue(':user_id', $this->authDataOrDefault('user_id', $this->auth_id), PDO::PARAM_STR);
        $prepared->bindValue(':display_name', $this->authDataOrDefault('display_name', null), PDO::PARAM_STR);
        $prepared->bindValue(':user_password', $this->authDataOrDefault('user_password', null), PDO::PARAM_STR);
        $prepared->bindValue(':active', $this->authDataOrDefault('active', 0), PDO::PARAM_INT);
        $prepared->bindValue(':failed_logins', $this->authDataOrDefault('failed_logins', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_failed_login', $this->authDataOrDefault('last_failed_login', 0), PDO::PARAM_INT);
        $database->executePrepared($prepared);

        foreach ($this->user_roles as $user_role)
        {
            $user_role->writeToDatabase();
        }
        return true;
    }

    public function boardRole($board_id, $return_id = false)
    {
        foreach ($this->user_roles as $user_role)
        {
            if ($user_role->auth_data['board'] === $board_id)
            {
                if ($return_id)
                {
                    return $user_role->auth_id;
                }
                else
                {
                    return $user_role->role;
                }

            }
        }

        return false;
    }

    public function userRole($board_id)
    {
        foreach ($this->user_roles as $user_role)
        {
            if ($user_role->auth_data['board'] === $board_id)
            {
                return $user_role;
            }
        }

        return false;
    }

    public function updateUserRole($board_id, $role_id)
    {
        foreach ($this->user_roles as $user_role)
        {
            if ($user_role->auth_data['board'] === $board_id)
            {
                $user_role->updateRole($role_id);
                return;
            }
        }
    }

    public function removeUserRole($board_id, $role_id)
    {
        foreach ($this->user_roles as $index => $user_role)
        {
            if ($user_role->auth_data['board'] === $board_id)
            {
                $user_role->removeFromDatabase();
                unset($this->user_roles[$index]);
            }
        }

        return false;
    }

    public function addUserRole($board_id, $role_id)
    {
        $user_role = new \Nelliel\AuthUserRole($this->database, $this->auth_id, $role_id);
        $user_role->auth_data['role_id'] = $role_id;
        $user_role->auth_data['board'] = $board_id;
        $user_role->auth_data['user_id'] = $this->auth_id;
        $user_role->writeToDatabase();
        $role = new \Nelliel\AuthRole($this->database, $role_id);
        $role->loadFromDatabase();
        $this->user_roles[] = $user_role;
    }

    public function boardPerm($board_id, $perm)
    {
        $role = $this->boardRole($board_id);

        if (!$role)
        {
            return false;
        }

        return $role->checkPermission($perm);
    }
}

