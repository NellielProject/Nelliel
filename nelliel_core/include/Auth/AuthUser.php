<?php
declare(strict_types = 1);

namespace Nelliel\Auth;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\NellielPDO;
use PDO;

class AuthUser extends AuthHandler
{
    private $user_roles = array();

    function __construct(NellielPDO $database, string $user_id, bool $db_load = true)
    {
        $this->database = $database;
        $this->empty = nel_true_empty($user_id);
        $this->auth_id = $user_id;
        $this->authorization = new Authorization($this->database);

        if ($db_load)
        {
            $this->loadFromDatabase();
        }
    }

    public function loadFromDatabase(): bool
    {
        if ($this->empty())
        {
            return false;
        }

        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_USERS_TABLE . '" WHERE "user_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC, true);

        if (empty($result))
        {
            return false;
        }

        $this->auth_data = $result;
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_USER_ROLES_TABLE . '" WHERE "user_id" = ?');
        $result = $this->database->executePreparedFetchAll($prepared, [$this->id()], PDO::FETCH_ASSOC, true);

        foreach ($result as $row)
        {
            $this->modifyRole($row['domain_id'], $row['role_id']);
        }

        return true;
    }

    public function writeToDatabase(): bool
    {
        if ($this->empty() || empty($this->id()))
        {
            return false;
        }

        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_USERS_TABLE . '" WHERE "user_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . NEL_USERS_TABLE .
                    '" SET "display_name" = :display_name, "user_password" = :user_password, "active" = :active, "owner" = :owner, "last_login" = :last_login WHERE "user_id" = :user_id');
        }
        else
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . NEL_USERS_TABLE .
                    '" ("user_id", "display_name", "user_password", "hashed_user_id", "active", "owner", "last_login") VALUES
                    (:user_id, :display_name, :user_password, :hashed_user_id, :active, :owner, :last_login)');
            $prepared->bindValue(':hashed_user_id', nel_prepare_hash_for_storage($this->getData('hashed_user_id')),
                    PDO::PARAM_LOB);
        }

        $prepared->bindValue(':user_id', $this->authDataOrDefault('user_id', $this->id()), PDO::PARAM_STR);
        $prepared->bindValue(':display_name', $this->authDataOrDefault('display_name', ''), PDO::PARAM_STR);
        $prepared->bindValue(':user_password', $this->authDataOrDefault('user_password', ''), PDO::PARAM_STR);
        $prepared->bindValue(':active', $this->authDataOrDefault('active', 0), PDO::PARAM_INT);
        $prepared->bindValue(':owner', $this->authDataOrDefault('owner', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_login', $this->authDataOrDefault('last_login', 0), PDO::PARAM_INT);
        $this->database->executePrepared($prepared);

        foreach ($this->user_roles as $domain_id => $user_role)
        {
            $prepared = $this->database->prepare(
                    'SELECT "entry" FROM "' . NEL_USER_ROLES_TABLE . '" WHERE "user_id" = ? AND "domain_id" = ?');
            $result = $this->database->executePreparedFetch($prepared, [$this->id(), $domain_id], PDO::FETCH_COLUMN);

            if ($result)
            {
                $prepared = $this->database->prepare(
                        'UPDATE "' . NEL_USER_ROLES_TABLE .
                        '" SET "user_id" = :user_id, "role_id" = :role_id, "domain_id" = :domain_id WHERE "entry" = :entry');
                $prepared->bindValue(':entry', $result, PDO::PARAM_INT);
            }
            else
            {
                $prepared = $this->database->prepare(
                        'INSERT INTO "' . NEL_USER_ROLES_TABLE .
                        '" ("user_id", "role_id", "domain_id") VALUES
                    (:user_id, :role_id, :domain_id)');
            }

            $prepared->bindValue(':user_id', $this->id(), PDO::PARAM_STR);
            $prepared->bindValue(':role_id', $user_role['role_id'], PDO::PARAM_STR);
            $prepared->bindValue(':domain_id', $domain_id, PDO::PARAM_STR);
            $this->database->executePrepared($prepared);
        }

        return true;
    }

    public function setupNew(): void
    {
        $this->changeData('hashed_user_id', hash('sha256', $this->id()));
    }

    public function remove(): void
    {
        if ($this->empty())
        {
            return;
        }

        $prepared = $this->database->prepare('DELETE FROM "' . NEL_USERS_TABLE . '" WHERE "user_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function updatePassword(string $new_password): void
    {
        $this->changeData('user_password', nel_password_hash($new_password, NEL_PASSWORD_ALGORITHM));
    }

    public function getDomainRole(Domain $domain): AuthRole
    {
        if ($this->empty() || !isset($this->user_roles[$domain->id()]))
        {
            return new AuthRole($this->database, '');
        }

        return $this->user_roles[$domain->id()]['role'];
    }

    public function modifyRole(string $domain_id, string $role_id): void
    {
        if (!isset($this->user_roles[$domain_id]))
        {
            $this->user_roles[$domain_id] = ['role_id' => $role_id, 'domain_id' => $domain_id,
                'role' => $this->setupAuthRole($role_id)];
        }
        else
        {
            $this->user_roles[$domain_id]['role_id'] = $role_id;
            $this->user_roles[$domain_id]['role'] = $this->setupAuthRole($role_id);
        }
    }

    public function removeRole(string $domain_id, string $role_id): void
    {
        if (!isset($this->user_roles[$domain_id]))
        {
            return;
        }

        $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_USER_ROLES_TABLE . '" WHERE "user_id" = ? AND "domain_id" = ?');
        $this->database->executePrepared($prepared, [$this->id(), $domain_id]);
        unset($this->user_roles[$domain_id]);
    }

    public function checkPermission(Domain $domain, string $permission): bool
    {
        // Site Owner can do all the things
        if ($this->isSiteOwner())
        {
            return true;
        }

        if ($this->empty())
        {
            return false;
        }

        $role = $this->getDomainRole($domain);

        if ($role->checkPermission($permission))
        {
            return true;
        }

        $site_role = $this->getDomainRole(nel_site_domain());

        if ($site_role->checkPermission($permission))
        {
            return true;
        }

        return false;
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
