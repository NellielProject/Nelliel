<?php

namespace Nelliel\Auth;

use PDO;
use Nelliel\NellielPDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class AuthRole extends AuthHandler
{
    public $permissions = array();

    function __construct(NellielPDO $database, string $role_id)
    {
        $this->database = $database;
        $this->auth_id = $role_id;
    }

    public function loadFromDatabase($temp_database = null)
    {
        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare('SELECT * FROM "' . ROLES_TABLE . '" WHERE "role_id" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC, true);

        if (empty($result))
        {
            return false;
        }

        $this->auth_data = $result;
        $this->permissions = new AuthPermissions($database, $this->id());
        $this->permissions->loadFromDatabase();
        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (empty($this->auth_data))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare('SELECT "entry" FROM "' . ROLES_TABLE . '" WHERE "role_id" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $database->prepare(
                    'UPDATE "' . ROLES_TABLE .
                    '" SET "role_id" = :role_id, "role_level" = :role_level, "role_title" = :role_title, "capcode" = :capcode WHERE "entry" = :entry');
            $prepared->bindValue(':entry', $result, PDO::PARAM_INT);
        }
        else
        {
            $prepared = $database->prepare(
                    'INSERT INTO "' . ROLES_TABLE . '" ("role_id", "role_level", "role_title", "capcode") VALUES
                    (:role_id, :role_level, :role_title, :capcode)');
        }

        $prepared->bindValue(':role_id', $this->authDataOrDefault('role_id', $this->id()), PDO::PARAM_STR);
        $prepared->bindValue(':role_level', $this->authDataOrDefault('role_level', 0), PDO::PARAM_INT);
        $prepared->bindValue(':role_title', $this->authDataOrDefault('role_title', null), PDO::PARAM_STR);
        $prepared->bindValue(':capcode', $this->authDataOrDefault('capcode', null), PDO::PARAM_STR);
        $database->executePrepared($prepared);
        $this->permissions->writeToDatabase();
        return true;
    }

    public function setupNew()
    {
        $this->permissions = new AuthPermissions($this->database, $this->id());
        $this->permissions->setupNew();
    }

    public function remove()
    {
        $authorization = new \Nelliel\Auth\Authorization($this->database);
        $prepared = $this->database->prepare(
                'SELECT "user_id", "board" FROM "' . USER_ROLES_TABLE . '" WHERE "role_id" = ?');
        $user_roles = $this->database->executePreparedFetchAll($prepared, [$this->id()], PDO::FETCH_ASSOC);

        foreach($user_roles as $user_role)
        {
            $authorization->getUser($user_role['user_id'])->removeRole($user_role['domain'], $this->id());
        }

        $prepared = $this->database->prepare('DELETE FROM "' . ROLE_PERMISSIONS_TABLE . '" WHERE "role_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
        $prepared = $this->database->prepare('DELETE FROM "' . ROLES_TABLE . '" WHERE "role_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function checkPermission(string $permission_id)
    {
        if (isset($this->permissions->auth_data[$permission_id]))
        {
            return $this->permissions->auth_data[$permission_id];
        }

        return false;
    }
}
