<?php
declare(strict_types = 1);

namespace Nelliel\Account;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Interfaces\MutableData;
use Nelliel\Interfaces\SelfPersisting;
use PDO;

class Role implements MutableData, SelfPersisting
{
    public RolePermissions $permissions;
    private NellielPDO $database;
    private array $data = array();
    private bool $empty;
    private bool $changed = false;
    private string $id = '';

    function __construct(NellielPDO $database, string $role_id, bool $db_load = true)
    {
        $this->database = $database;
        $this->empty = nel_true_empty($role_id);
        $this->id = utf8_strtolower($role_id);
        $this->permissions = new RolePermissions($this->database, $this->id);

        if ($db_load) {
            $this->load();
        }
    }

    public function load(): void
    {
        if ($this->empty) {
            return;
        }

        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_ROLES_TABLE . '" WHERE "role_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);

        if (empty($result)) {
            return;
        }

        $this->data = $result;
        $this->permissions->load();
    }

    public function save(): void
    {
        if (!$this->changed || empty($this->data) || empty($this->id())) {
            return;
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

        $prepared->bindValue(':role_id', $this->getData('role_id') ?? $this->id(), PDO::PARAM_STR);
        $prepared->bindValue(':role_level', $this->getData('role_level') ?? 0, PDO::PARAM_INT);
        $prepared->bindValue(':role_title', $this->getData('role_title'), PDO::PARAM_STR);
        $prepared->bindValue(':capcode', $this->getData('capcode'), PDO::PARAM_STR);

        if ($this->database->executePrepared($prepared)) {
            $this->changed = false;
        }

        if ($this->getData('role_id') !== $this->id()) {
            $this->id = $this->getData('role_id');
            $this->permissions->changeID($this->getData('role_id'));
        }

        $this->permissions->save();
    }

    public function delete(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_ROLES_TABLE . '" WHERE "role_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
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

    public function getData(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function changeData(string $key, $value): void
    {
        $this->changed = true;
        $this->data[$key] = $value;
    }

    public function id()
    {
        return $this->id;
    }

    public function empty()
    {
        return $this->empty;
    }
}
