<?php
declare(strict_types = 1);

namespace Nelliel\Auth;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use PDO;

class AuthUser extends AuthHandler
{
    private $user_roles = array();

    function __construct(NellielPDO $database, string $username, bool $db_load = true)
    {
        $this->database = $database;
        $this->empty = nel_true_empty($username);
        $this->auth_id = utf8_strtolower($username);
        $this->authorization = new Authorization($this->database);

        if ($db_load) {
            $this->loadFromDatabase();
        }
    }

    public function loadFromDatabase(): bool
    {
        if ($this->empty()) {
            return false;
        }

        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_USERS_TABLE . '" WHERE "username" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);

        if (empty($result)) {
            return false;
        }

        $this->auth_data = $result;
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_USER_ROLES_TABLE . '" WHERE "username" = ?');
        $result = $this->database->executePreparedFetchAll($prepared, [$this->id()], PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            $this->modifyRole($row['domain_id'], $row['role_id']);
        }

        return true;
    }

    public function writeToDatabase(): bool
    {
        if ($this->empty() || empty($this->id())) {
            return false;
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

        $prepared->bindValue(':username', $this->authDataOrDefault('username', $this->id()), PDO::PARAM_STR);
        $prepared->bindValue(':display_name', $this->authDataOrDefault('display_name', ''), PDO::PARAM_STR);
        $prepared->bindValue(':password', $this->authDataOrDefault('password', ''), PDO::PARAM_STR);
        $prepared->bindValue(':active', $this->authDataOrDefault('active', 0), PDO::PARAM_INT);
        $prepared->bindValue(':owner', $this->authDataOrDefault('owner', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_login', $this->authDataOrDefault('last_login', 0), PDO::PARAM_INT);

        if ($this->database->executePrepared($prepared)) {
            $this->changed = false;
        }

        if ($this->getData('username') !== $this->id()) {
            $this->auth_id = $this->getData('username');
        }

        foreach ($this->user_roles as $domain_id => $user_role) {
            $prepared = $this->database->prepare(
                'SELECT 1 FROM "' . NEL_USER_ROLES_TABLE . '" WHERE "username" = ? AND "domain_id" = ?');
            $result = $this->database->executePreparedFetch($prepared, [$this->id(), $domain_id], PDO::FETCH_COLUMN);

            if ($result) {
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

        return true;
    }

    public function exists(): bool
    {
        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_USERS_TABLE . '" WHERE "username" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_COLUMN);
        return !empty($result);
    }

    public function remove(): void
    {
        if ($this->empty()) {
            return;
        }

        $prepared = $this->database->prepare('DELETE FROM "' . NEL_USERS_TABLE . '" WHERE "username" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function updatePassword(string $new_password): void
    {
        $this->changeData('password', nel_password_hash($new_password, nel_crypt_config()->accountPasswordAlgorithm(),
            nel_crypt_config()->accountPasswordOptions()));
    }

    public function getDomainRole(Domain $domain): AuthRole
    {
        if ($this->empty() || !isset($this->user_roles[$domain->id()])) {
            return new AuthRole($this->database, '');
        }

        return $this->user_roles[$domain->id()]['role'];
    }

    public function modifyRole(string $domain_id, string $role_id): void
    {
        if (!isset($this->user_roles[$domain_id])) {
            $this->user_roles[$domain_id] = ['role_id' => $role_id, 'domain_id' => $domain_id,
                'role' => $this->setupAuthRole($role_id)];
        } else {
            $this->user_roles[$domain_id]['role_id'] = $role_id;
            $this->user_roles[$domain_id]['role'] = $this->setupAuthRole($role_id);
        }
    }

    public function removeRole(string $domain_id, string $role_id): void
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
        // Site Owner can do all the things
        if ($this->isSiteOwner()) {
            return true;
        }

        if ($this->empty()) {
            return false;
        }

        $role = $this->getDomainRole($domain);

        if ($role->checkPermission($permission)) {
            return true;
        }

        if ($domain->id() === Domain::SITE || !$escalate) {
            return false;
        }

        $global_role = $this->getDomainRole(nel_global_domain());

        if ($global_role->checkPermission($permission)) {
            return true;
        }

        $site_role = $this->getDomainRole(nel_site_domain());
        return $site_role->checkPermission($permission);
    }

    private function setupAuthRole(string $role_id): AuthRole
    {
        $role = new AuthRole($this->database, $role_id);
        $role->loadFromDatabase();
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
}
