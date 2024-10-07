<?php
declare(strict_types = 1);

namespace Nelliel\Account;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Interfaces\MutableData;
use Nelliel\Interfaces\SelfPersisting;
use PDO;

class RolePermissions implements MutableData, SelfPersisting
{
    private NellielPDO $database;
    private array $data = array();
    private bool $changed = false;
    private string $id = '';

    function __construct(NellielPDO $database, string $role_id, bool $db_load = true)
    {
        $this->database = $database;
        $this->id = utf8_strtolower($role_id);

        if ($db_load) {
            $this->load();
        }
    }

    public function load(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_ROLE_PERMISSIONS_TABLE . '" WHERE "role_id" = ?');
        $result = $this->database->executePreparedFetchAll($prepared, [$this->id()], PDO::FETCH_ASSOC, true);

        if (empty($result)) {
            return;
        }

        foreach ($result as $perm) {
            $this->changeData($perm['permission'], boolval($perm['perm_setting']));
        }
    }

    public function save(): void
    {
        if (empty($this->data)) {
            return;
        }

        foreach ($this->data as $perm => $setting) {
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
    }

    public function delete(): void
    {}

    public function exists(): bool
    {
        return true;
    }

    public function checkPermission(string $permission_id): bool
    {
        return boolval($this->getData($permission_id));
    }

    public function getData(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function changeData(string $key, $value): void
    {
        $this->changed = true;
        $this->data[$key] = $value;
    }

    public function setPermission(string $key, bool $value): void
    {
        $this->changeData($key, $value);
    }

    public function changeID(string $new_id): void
    {
        $this->id = $new_id;
    }

    public function id()
    {
        return $this->id;
    }
}

