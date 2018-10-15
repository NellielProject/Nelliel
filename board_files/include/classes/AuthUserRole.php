<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class AuthUserRole extends AuthBase
{
    public $permissions = array();
    public $user_id;
    public $role;
    public $board;

    function __construct($database, $user_id, $role_id, $board)
    {
        $this->database = $database;
        $this->user_id = $user_id;
        $this->auth_id = $role_id;
        $this->board = $board;
        $this->role = new \Nelliel\AuthRole($this->database, $role_id);
        $this->role->loadFromDatabase();
    }

    public function loadFromDatabase($temp_database = null)
    {
        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare('SELECT * FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = ? AND "role_id" = ? AND "board" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->user_id, $this->auth_id, $this->board], PDO::FETCH_ASSOC,
                true);

        if (empty($result))
        {
            return false;
        }

        $this->auth_data = $result;
        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (empty($this->auth_data))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare(
                'SELECT "entry" FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = ? AND "role_id" = ? AND "board" = ?');
        $result = $database->executePreparedFetch($prepared,
                [$this->user_id, $this->auth_data['role_id'], $this->auth_data['board']], PDO::FETCH_COLUMN);

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

        $prepared->bindValue(':user_id', $this->user_id, PDO::PARAM_STR);
        $prepared->bindValue(':role_id', $this->auth_id, PDO::PARAM_STR);
        $prepared->bindValue(':board', $this->auth_data['board'], PDO::PARAM_STR);
        $database->executePrepared($prepared);
        return true;
    }

    public function updateRole($role_id)
    {
        $this->auth_id = $role_id;
        $this->writeToDatabase();
        $this->loadFromDatabase();
        $this->role = new \Nelliel\AuthRole($this->database, $role_id);
        $this->role->loadFromDatabase();
    }

    public function removeFromDatabase()
    {
        $prepared = $this->database->prepare(
                'DELETE FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = ? AND "role_id" = ? AND "board" = ?');
        $this->database->executePrepared($prepared,
                [$this->user_id, $this->auth_id, $this->auth_data['board']]);
    }
}

