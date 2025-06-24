<?php
declare(strict_types = 1);

namespace Nelliel\Account;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use Nelliel\Interfaces\MutableData;
use Nelliel\Interfaces\SelfPersisting;
use PDO;

class User implements MutableData, SelfPersisting
{
    private NellielPDO $database;
    private array $user_roles = array();
    private array $data = array();
    private bool $empty;
    private bool $changed = false;
    private string $id = '';

    function __construct(NellielPDO $database, string $username, bool $db_load = true)
    {
        $this->database = $database;
        $this->empty = nel_true_empty($username);
        $this->id = utf8_strtolower($username);

        if ($db_load) {
            $this->load();
        }
    }

    public function load(): void
    {
        if ($this->empty()) {
            return;
        }

        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_USERS_TABLE . '" WHERE "username" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);

        if (empty($result)) {
            return;
        }

        $this->data = $result;
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_USER_ROLES_TABLE . '" WHERE "username" = ?');
        $result = $this->database->executePreparedFetchAll($prepared, [$this->id()], PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            $this->modifyRole($row['domain_id'], $row['role_id']);
        }
    }

    public function save(): void
    {
        if ($this->empty() || empty($this->data) || empty($this->id()) || !$this->changed) {
            return;
        }

        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_USERS_TABLE . '" WHERE "username" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_COLUMN);

        if ($result) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_USERS_TABLE .
                '" SET "username" = :username, "display_name" = :display_name, "password" = :password, "active" = :active, "owner" = :owner, "last_login" = :last_login WHERE "username" = :last_username');
            $prepared->bindValue(':last_username', $this->id(), PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_USERS_TABLE .
                '" ("username", "display_name", "password", "active", "owner", "last_login") VALUES
                    (:username, :display_name, :password, :active, :owner, :last_login)');
        }

        $prepared->bindValue(':username', $this->getData('username') ?? $this->id(), PDO::PARAM_STR);
        $prepared->bindValue(':display_name', $this->getData('display_name') ?? '', PDO::PARAM_STR);
        $prepared->bindValue(':password', $this->getData('password') ?? '', PDO::PARAM_STR);
        $prepared->bindValue(':active', $this->getData('active') ?? 0, PDO::PARAM_INT);
        $prepared->bindValue(':owner', $this->getData('owner') ?? 0, PDO::PARAM_INT);
        $prepared->bindValue(':last_login', $this->getData('last_login') ?? 0, PDO::PARAM_INT);

        if ($this->database->executePrepared($prepared)) {
            $this->changed = false;
        }

        if (!nel_true_empty($this->getData('username')) && $this->getData('username') !== $this->id()) {
            $this->id = $this->getData('username');
        }

        foreach ($this->user_roles as $domain_id => $user_role) {
            if (nel_true_empty($user_role['role_id'])) {
                continue;
            }

            if ($this->database->rowExists(NEL_USER_ROLES_TABLE, ['username', 'domain_id'], [$this->id(), $domain_id])) {
                $prepared = $this->database->prepare(
                    'UPDATE "' . NEL_USER_ROLES_TABLE .
                    '" SET "role_id" = :role_id WHERE "username" = :username AND "domain_id" = :domain_id');
            } else {
                $prepared = $this->database->prepare(
                    'INSERT INTO "' . NEL_USER_ROLES_TABLE .
                    '" ("username", "role_id", "domain_id") VALUES
                    (:username, :role_id, :domain_id)');
            }

            $prepared->bindValue(':username', $this->id(), PDO::PARAM_STR);
            $prepared->bindValue(':role_id', $user_role['role_id'], PDO::PARAM_STR);
            $prepared->bindValue(':domain_id', $domain_id, PDO::PARAM_STR);
            $this->database->executePrepared($prepared);
        }
    }

    public function delete(): void
    {
        if ($this->empty() || empty($this->id())) {
            return;
        }

        $prepared = $this->database->prepare('DELETE FROM "' . NEL_USERS_TABLE . '" WHERE "username" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function exists(): bool
    {
        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_USERS_TABLE . '" WHERE "username" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_COLUMN);
        return !empty($result);
    }

    public function updatePassword(string $new_password): void
    {
        $this->changeData('password',
            nel_password_hash($new_password, nel_crypt_config()->accountPasswordAlgorithm(),
                nel_crypt_config()->accountPasswordOptions()));
    }

    public function getDomainRole(Domain $domain): Role
    {
        if ($this->empty() || !isset($this->user_roles[$domain->id()])) {
            return new Role($this->database, '');
        }

        return $this->user_roles[$domain->id()]['role'];
    }

    public function modifyRole(string $domain_id, string $role_id): void
    {
        if (nel_true_empty($role_id)) {
            $this->removeRole($domain_id);
        }

        $this->user_roles[$domain_id]['role_id'] = $role_id;
        $this->user_roles[$domain_id]['role'] = $this->setupRole($role_id);
    }

    public function removeRole(string $domain_id): void
    {
        if (!isset($this->user_roles[$domain_id])) {
            return;
        }

        $prepared = $this->database->prepare(
            'DELETE FROM "' . NEL_USER_ROLES_TABLE . '" WHERE "username" = ? AND "domain_id" = ?');
        $this->database->executePrepared($prepared, [$this->id(), $domain_id]);
        unset($this->user_roles[$domain_id]);
    }

    public function checkPermission(Domain $domain, string $permission, bool $escalate = true): bool
    {
        if ($this->empty()) {
            return false;
        }

        // Site Owner can do all the things
        if ($this->isSiteOwner()) {
            return true;
        }

        $role = $this->getDomainRole($domain);

        if ($role->checkPermission($permission)) {
            return true;
        }

        if ($domain->id() === Domain::SITE || !$escalate) {
            return false;
        }

        $global_role = $this->getDomainRole(nel_get_cached_domain(Domain::GLOBAL));

        if ($global_role->checkPermission($permission)) {
            return true;
        }

        $site_role = $this->getDomainRole(nel_get_cached_domain(Domain::SITE));
        return $site_role->checkPermission($permission);
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

    private function setupRole(string $role_id): Role
    {
        $role = new Role($this->database, $role_id);
        return $role;
    }

    public function active(): bool
    {
        return $this->getData('active') || $this->isSiteOwner();
    }

    public function isSiteOwner(): bool
    {
        return $this->getData('owner') == 1;
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
