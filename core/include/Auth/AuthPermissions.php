<?php
declare(strict_types = 1);

namespace Nelliel\Auth;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;
use PDO;

class AuthPermissions extends AuthHandler
{

    function __construct(NellielPDO $database, string $role_id, bool $db_load = true)
    {
        $this->database = $database;
        $this->auth_id = utf8_strtolower($role_id);

        if ($db_load) {
            $this->loadFromDatabase();
        }
    }

    public function loadFromDatabase(): bool
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_ROLE_PERMISSIONS_TABLE . '" WHERE "role_id" = ?');
        $result = $this->database->executePreparedFetchAll($prepared, [$this->id()], PDO::FETCH_ASSOC, true);

        if (empty($result)) {
            return false;
        }

        foreach ($result as $perm) {
            $this->changeData($perm['permission'], boolval($perm['perm_setting']));
        }

        return true;
    }

    public function writeToDatabase(): bool
    {
        if (empty($this->auth_data)) {
            return false;
        }

        foreach ($this->auth_data as $perm => $setting) {
            $prepared = $this->database->prepare(
                'SELECT 1 FROM "' . NEL_ROLE_PERMISSIONS_TABLE . '" WHERE "role_id" = ? AND "permission" = ?');
            $result = $this->database->executePreparedFetch($prepared, [$this->id(), $perm], PDO::FETCH_COLUMN);

            if ($result) {
                $prepared = $this->database->prepare(
                    'UPDATE "' . NEL_ROLE_PERMISSIONS_TABLE .
                    '" SET "role_id" = :role_id, "permission" = :permission, "perm_setting" = :perm_setting WHERE "role_id" = :role_id2 AND "permission" = :permission2');
                $prepared->bindValue(':role_id2', $this->id(), PDO::PARAM_STR);
                $prepared->bindValue(':permission2', $perm, PDO::PARAM_STR);
            } else {
                $prepared = $this->database->prepare(
                    'INSERT INTO "' . NEL_ROLE_PERMISSIONS_TABLE .
                    '" ("role_id", "permission", "perm_setting") VALUES
                    (:role_id, :permission, :perm_setting)');
            }

            $prepared->bindValue(':role_id', $this->id(), PDO::PARAM_STR);
            $prepared->bindValue(':permission', $perm, PDO::PARAM_STR);
            $prepared->bindValue(':perm_setting', intval($setting), PDO::PARAM_INT);
            $this->database->executePrepared($prepared);
        }

        return true;
    }

    public function exists(): bool
    {
        return true;
    }

    public function remove(): void
    {}

    public function checkPermission(string $permission_id): bool
    {
        return $this->authDataOrDefault($permission_id, false);
    }

    public function setPermission(string $key, bool $value): void
    {
        $this->changeData($key, $value);
    }

    public function changeID(string $new_id): void
    {
        $this->auth_id = $new_id;
    }
}

