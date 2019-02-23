<?php

namespace Nelliel\Auth;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class AuthUser extends AuthHandler
{
    public $user_roles = array();

    function __construct($database, $user_id)
    {
        $this->database = $database;
        $this->auth_id = $user_id;
    }

    public function loadFromDatabase($temp_database = null)
    {
        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare('SELECT * FROM "' . USERS_TABLE . '" WHERE "user_id" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->auth_id], PDO::FETCH_ASSOC, true);

        if (empty($result))
        {
            return false;
        }

        $this->auth_data = $result;
        $prepared = $database->prepare('SELECT * FROM "' . USER_ROLES_TABLE . '" WHERE "user_id" = ?');
        $result = $database->executePreparedFetchAll($prepared, [$this->auth_id], PDO::FETCH_ASSOC, true);

        foreach ($result as $row)
        {
            $this->changeOrAddRole($row['scope'], $row['domain_id'], $row['role_id']);
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
        $prepared = $database->prepare('SELECT "entry" FROM "' . USERS_TABLE . '" WHERE "user_id" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->auth_id], PDO::FETCH_COLUMN);

        if ($result)
        {
            $prepared = $database->prepare(
                    'UPDATE "' . USERS_TABLE .
                    '" SET "user_id" = :user_id, "display_name" = :display_name, "user_password" = :user_password, "active" = :active, "super_admin" = :super_admin, "last_login" = :last_login WHERE "entry" = :entry');
            $prepared->bindValue(':entry', $result, PDO::PARAM_INT);
        }
        else
        {
            $prepared = $database->prepare(
                    'INSERT INTO "' . USERS_TABLE . '" ("user_id", "display_name", "user_password", "active", "super_admin", "last_login") VALUES
                    (:user_id, :display_name, :user_password, :active, :super_admin, :last_login)');
        }

        $prepared->bindValue(':user_id', $this->authDataOrDefault('user_id', $this->auth_id), PDO::PARAM_STR);
        $prepared->bindValue(':display_name', $this->authDataOrDefault('display_name', null), PDO::PARAM_STR);
        $prepared->bindValue(':user_password', $this->authDataOrDefault('user_password', null), PDO::PARAM_STR);
        $prepared->bindValue(':active', $this->authDataOrDefault('active', 0), PDO::PARAM_INT);
        $prepared->bindValue(':super_admin', $this->authDataOrDefault('super_admin', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_login', $this->authDataOrDefault('last_login', 0), PDO::PARAM_INT);
        $database->executePrepared($prepared);

        foreach ($this->user_roles as $scope => $user_roles)
        {
            foreach ($user_roles as $user_role)
            {

                $prepared = $database->prepare(
                        'SELECT "entry" FROM "' . USER_ROLES_TABLE .
                        '" WHERE "user_id" = ? AND "scope" = ? AND "domain_id" = ?');
                $result = $database->executePreparedFetch($prepared, [$this->auth_id, $scope,
                    $user_role['domain_id']], PDO::FETCH_COLUMN);

                if ($result)
                {
                    $prepared = $database->prepare(
                            'UPDATE "' . USER_ROLES_TABLE .
                            '" SET "user_id" = :user_id, "role_id" = :role_id, "scope" = :scope, "domain_id" = :domain_id WHERE "entry" = :entry');
                    $prepared->bindValue(':entry', $result, PDO::PARAM_INT);
                }
                else
                {
                    $prepared = $database->prepare(
                            'INSERT INTO "' . USER_ROLES_TABLE . '" ("user_id", "role_id", "scope", "domain_id") VALUES
                    (:user_id, :role_id, :scope, :domain_id)');
                }

                $prepared->bindValue(':user_id', $this->auth_id, PDO::PARAM_STR);
                $prepared->bindValue(':role_id', $user_role['role_id'], PDO::PARAM_STR);
                $prepared->bindValue(':scope', $scope, PDO::PARAM_STR);
                $prepared->bindValue(':domain_id', $user_role['domain_id'], PDO::PARAM_STR);
                $database->executePrepared($prepared);
            }
        }

        return true;
    }

    public function setupNew()
    {
    }

    public function remove()
    {
        $prepared = $this->database->prepare('DELETE FROM "' . USER_ROLES_TABLE . '" WHERE "user_id" = ?');
        $this->database->executePrepared($prepared, [$this->auth_id]);
        $prepared = $this->database->prepare('DELETE FROM "' . USERS_TABLE . '" WHERE "user_id" = ?');
        $this->database->executePrepared($prepared, [$this->auth_id]);
    }

    public function domainRole(Domain $domain, bool $return_id = false, bool $escalate = true)
    {
        if (!isset($this->user_roles[$domain->scope()]))
        {
            return false;
        }

        foreach ($this->user_roles[$domain->scope()] as $user_role)
        {
            if ($user_role['domain_id'] === $domain->id())
            {
                if ($return_id)
                {
                    return $user_role['role_id'];
                }
                else
                {
                    return $user_role['role'];
                }
            }

            if ($escalate && $user_role['board'] === '')
            {
                if ($return_id)
                {
                    return $user_role['role_id'];
                }
                else
                {
                    return $user_role['role'];
                }
            }
        }

        return false;
    }

    public function changeOrAddRole($scope, $domain_id, $role_id)
    {
        if (!isset($this->user_roles[$scope]))
        {
            $this->user_roles[$scope] = array();
        }

        foreach ($this->user_roles[$scope] as $index => $user_role)
        {
            if ($user_role['domain_id'] === $domain_id)
            {
                $this->user_roles[$scope][$index]['role_id'] = $role_id;
                $this->user_roles[$scope][$index]['role'] = $this->setupAuthRole($role_id);
                return;
            }
        }

        $this->user_roles[$scope][] = ['role_id' => $role_id, 'domain_id' => $domain_id,
            'role' => $this->setupAuthRole($role_id)];
    }

    public function removeRole($scope, $domain_id, $role_id)
    {
        if (!isset($this->user_roles[$scope]))
        {
            return;
        }

        foreach ($this->user_roles[$scope] as $index => $user_role)
        {
            if ($user_role['domain_id'] === $domain_id)
            {
                $prepared = $this->database->prepare(
                        'DELETE FROM "' . USER_ROLES_TABLE . '" WHERE "user_id" = ? AND "domain_id" = ?');
                $this->database->executePrepared($prepared, [$this->auth_id, $domain_id]);
                unset($this->user_roles[$scope][$index]);
            }
        }
    }

    public function domainPermission(Domain $domain, $perm_id, bool $escalate = true)
    {
        if ($this->isSuperAdmin())
        {
            return true;
        }

        $role_perm = false;
        $role = $this->domainRole($domain);

        if ($role && $role->checkPermission($perm_id))
        {
            return true;
        }

        if ($escalate) // TODO: Better way to escalate
        {
            $temp_domain = new \Nelliel\DomainBoard('ALL_BOARDS', new \Nelliel\CacheHandler(), $this->database);
            $role = $this->domainRole($temp_domain);

            if ($role && $role->checkPermission($perm_id))
            {
                return true;
            }

            $temp_domain = new \Nelliel\DomainSite(new \Nelliel\CacheHandler(), $this->database);
            $role = $this->domainRole($temp_domain);

            if ($role && $role->checkPermission($perm_id))
            {
                return true;
            }
        }

        return false;
    }

    private function setupAuthRole($role_id)
    {
        $role = new AuthRole($this->database, $role_id);
        $role->loadFromDatabase();
        return $role;
    }

    public function active()
    {
        return boolval($this->auth_data['active']);
    }

    public function isSuperAdmin()
    {
        return boolval($this->auth_data['super_admin']);
    }
}
