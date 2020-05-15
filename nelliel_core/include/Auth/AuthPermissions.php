<?php

namespace Nelliel\Auth;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\NellielPDO;
use PDO;

class AuthPermissions extends AuthHandler
{

    function __construct(NellielPDO $database, string $role_id, bool $db_load = true)
    {
        $this->database = $database;
        $this->auth_id = $role_id;

        if ($db_load)
        {
            $this->loadFromDatabase();
        }
    }

    public function loadFromDatabase()
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_ROLE_PERMISSIONS_TABLE . '" WHERE "role_id" = ?');
        $result = $this->database->executePreparedFetchAll($prepared, [$this->id()], PDO::FETCH_ASSOC, true);

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

    public function writeToDatabase()
    {
        if (empty($this->auth_data))
        {
            return false;
        }

        foreach ($this->auth_data as $perm => $setting)
        {
            $prepared = $this->database->prepare(
                    'SELECT "entry" FROM "' . NEL_ROLE_PERMISSIONS_TABLE . '" WHERE "role_id" = ? AND "perm_id" = ?');
            $result = $this->database->executePreparedFetch($prepared, [$this->id(), $perm], PDO::FETCH_COLUMN);

            if ($result)
            {
                $prepared = $this->database->prepare(
                        'UPDATE "' . NEL_ROLE_PERMISSIONS_TABLE .
                        '" SET "role_id" = :role_id, "perm_id" = :perm_id, "perm_setting" = :perm_setting WHERE "entry" = :entry');
                $prepared->bindValue(':entry', $result, PDO::PARAM_INT);
            }
            else
            {
                $prepared = $this->database->prepare(
                        'INSERT INTO "' . NEL_ROLE_PERMISSIONS_TABLE . '" ("role_id", "perm_id", "perm_setting") VALUES
                    (:role_id, :perm_id, :perm_setting)');
            }

            $prepared->bindValue(':role_id', $this->authDataOrDefault('role_id', $this->id()), PDO::PARAM_STR);
            $prepared->bindValue(':perm_id', $perm, PDO::PARAM_STR);
            $prepared->bindValue(':perm_setting', intval($setting), PDO::PARAM_INT);
            $this->database->executePrepared($prepared);
        }
        return true;
    }

    public function setupNew()
    {
    }

    public function remove()
    {
    }
}

