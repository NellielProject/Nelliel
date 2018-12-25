<?php

namespace Nelliel\Auth;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class AuthPermissions extends AuthBase
{

    function __construct($database, $role_id)
    {
        $this->database = $database;
        $this->auth_id = $role_id;
    }

    public function loadFromDatabase($temp_database = null)
    {
        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare('SELECT * FROM "' . ROLE_PERMISSIONS_TABLE . '" WHERE "role_id" = ?');
        $result = $database->executePreparedFetchAll($prepared, [$this->auth_id], PDO::FETCH_ASSOC, true);

        if (empty($result))
        {
            return false;
        }

        foreach ($result as $perm)
        {
            $this->auth_data[$perm['perm_id']] = (bool) $perm['perm_setting'];
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

        foreach ($this->auth_data as $perm => $setting)
        {
            $prepared = $database->prepare(
                    'SELECT "entry" FROM "' . ROLE_PERMISSIONS_TABLE . '" WHERE "role_id" = ? AND "perm_id" = ?');
            $result = $database->executePreparedFetch($prepared, [$this->auth_id, $perm], PDO::FETCH_COLUMN);

            if ($result)
            {
                $prepared = $database->prepare(
                        'UPDATE "' . ROLE_PERMISSIONS_TABLE .
                        '" SET "role_id" = :role_id, "perm_id" = :perm_id, "perm_setting" = :perm_setting WHERE "entry" = :entry');
                $prepared->bindValue(':entry', $result, PDO::PARAM_INT);
            }
            else
            {
                $prepared = $database->prepare(
                        'INSERT INTO "' . ROLE_PERMISSIONS_TABLE . '" ("role_id", "perm_id", "perm_setting") VALUES
                    (:role_id, :perm_id, :perm_setting)');
            }

            $prepared->bindValue(':role_id', $this->authDataOrDefault('role_id', $this->auth_id), PDO::PARAM_STR);
            $prepared->bindValue(':perm_id', $perm, PDO::PARAM_STR);
            $prepared->bindValue(':perm_setting', intval($setting), PDO::PARAM_INT);
            $database->executePrepared($prepared);
        }
        return true;
    }

    public function setupNew()
    {
    }
}

