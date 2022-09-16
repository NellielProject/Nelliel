<?php
declare(strict_types = 1);

namespace Nelliel\Auth;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;
use PDO;

class AuthRole extends AuthHandler
{
    public $permissions;

    function __construct(NellielPDO $database, string $role_id, bool $db_load = true)
    {
        $this->database = $database;
        $this->empty = nel_true_empty($role_id);
        $this->auth_id = utf8_strtolower($role_id);
        $this->permissions = new AuthPermissions($this->database, $this->id());

        if ($db_load) {
            $this->loadFromDatabase();
        }
    }

    public function loadFromDatabase(): bool
    {
        if ($this->empty) {
            return false;
        }

        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_ROLES_TABLE . '" WHERE "role_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);

        if (empty($result)) {
            return false;
        }

        $this->auth_data = $result;
        $this->permissions->loadFromDatabase();
        return true;
    }

    public function writeToDatabase(): bool
    {
        if (empty($this->auth_data)) {
            return false;
        }

        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_ROLES_TABLE . '" WHERE "role_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_COLUMN);

        if ($result) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_ROLES_TABLE .
                '" SET "role_id" = :role_id, "role_level" = :role_level, "role_title" = :role_title, "capcode" = :capcode WHERE "role_id" = :current_role_id');
            $prepared->bindValue(':current_role_id', $this->id(), PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_ROLES_TABLE .
                '" ("role_id", "role_level", "role_title", "capcode") VALUES
                    (:role_id, :role_level, :role_title, :capcode)');
        }

        $prepared->bindValue(':role_id', $this->authDataOrDefault('role_id', $this->id()), PDO::PARAM_STR);
        $prepared->bindValue(':role_level', $this->authDataOrDefault('role_level', 0), PDO::PARAM_INT);
        $prepared->bindValue(':role_title', $this->authDataOrDefault('role_title', null), PDO::PARAM_STR);
        $prepared->bindValue(':capcode', $this->authDataOrDefault('capcode', null), PDO::PARAM_STR);

        if ($this->database->executePrepared($prepared)) {
            $this->changed = false;
        }

        if ($this->getData('role_id') !== $this->id()) {
            $this->auth_id = $this->getData('role_id');
            $this->permissions->changeID($this->getData('role_id'));
        }

        $this->permissions->writeToDatabase();
        return true;
    }

    public function exists(): bool
    {
        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_ROLES_TABLE . '" WHERE "role_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_COLUMN);
        return !empty($result);
    }

    public function remove(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_ROLES_TABLE . '" WHERE "role_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function checkPermission(string $permission_id): bool
    {
        return $this->permissions->checkPermission($permission_id);
    }
}
