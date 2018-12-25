<?php

namespace Nelliel\Auth;

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
        $prepared = $database->prepare('SELECT * FROM "' . USER_TABLE . '" WHERE "user_id" = ?');
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

            $role = $this->setupAuthRole($row['role_id']);
            $this->user_roles[] = ['role_id' => $row['role_id'], 'board' => $row['board'], 'role' => $role];
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
            $prepared = $database->prepare(
                    'SELECT "entry" FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = ? AND "board" = ?');
            $result = $database->executePreparedFetch($prepared, [$this->auth_id, $user_role['board']],
                    PDO::FETCH_COLUMN);

            if ($result)
            {
                $prepared = $database->prepare(
                        'UPDATE "' . USER_ROLE_TABLE .
                        '" SET "user_id" = :user_id, "role_id" = :role_id, "board" = :board WHERE "entry" = :entry');
                $prepared->bindValue(':entry', $result, PDO::PARAM_INT);
            }
            else
            {
                $prepared = $database->prepare(
                        'INSERT INTO "' . USER_ROLE_TABLE . '" ("user_id", "role_id", "board") VALUES
                    (:user_id, :role_id, :board)');
            }

            $prepared->bindValue(':user_id', $this->auth_id, PDO::PARAM_STR);
            $prepared->bindValue(':role_id', $user_role['role_id'], PDO::PARAM_STR);
            $prepared->bindValue(':board', $user_role['board'], PDO::PARAM_STR);
            $database->executePrepared($prepared);
        }

        return true;
    }

    public function setupNew()
    {
    }

    public function boardRole($board_id, $return_id = false, $check_allboard = true)
    {
        foreach ($this->user_roles as $user_role)
        {
            if ($user_role['board'] === $board_id)
            {
                if ($return_id)
                {
                    return $user_role['role_id'];
                }
                else
                {
                    return $user_role['role'];
                }
            }

            if ($check_allboard && $user_role['board'] === '')
            {
                if ($return_id)
                {
                    return $user_role['role_id'];
                }
                else
                {
                    return $user_role['role'];
                }
            }
        }

        return false;
    }

    public function changeOrAddBoardRole($board_id, $role_id)
    {
        foreach ($this->user_roles as $index => $user_role)
        {
            if ($user_role['board'] === $board_id)
            {
                $this->user_roles[$index]['role_id'] = $role_id;
                $this->user_roles[$index]['role'] = $this->setupAuthRole($role_id);
                return;
            }
        }

        $this->user_roles[] = ['role_id' => $role_id, 'board' => $board_id, 'role' => $this->setupAuthRole(
                $role_id)];
        $prepared = $this->database->prepare(
                'INSERT INTO "' . USER_ROLE_TABLE . '" ("user_id", "role_id", "board") VALUES
                    (?, ?, ?)');
        $this->database->executePrepared($prepared, [$this->auth_id, $role_id, $board_id]);
    }

    public function removeBoardRole($board_id, $role_id)
    {
        foreach ($this->user_roles as $index => $user_role)
        {
            if ($user_role['board'] === $board_id)
            {
                $prepared = $this->database->prepare(
                        'DELETE FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = ? AND "board" = ?');
                $this->database->executePrepared($prepared, [$this->auth_id, $board_id]);
                unset($this->user_roles[$index]);
            }
        }

        return false;
    }

    public function boardPerm($board_id, $perm, $check_allboard = true)
    {
        $role_perm = false;
        $role2_perm = false;

        $role = $this->boardRole($board_id);

        if ($role)
        {
            $role_perm = $role->checkPermission($perm);
        }

        if ($check_allboard && $board_id !== '')
        {
            $role2 = $this->boardRole('');

            if ($role2)
            {
                $role2_perm = $role2->checkPermission($perm);
            }
        }

        return $role_perm || $role2_perm;
    }

    private function setupAuthRole($role_id)
    {
        $role = new AuthRole($this->database, $role_id);
        $role->loadFromDatabase();
        return $role;
    }
}

