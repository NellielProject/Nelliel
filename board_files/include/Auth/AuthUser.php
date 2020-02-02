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
        $result = $database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC, true);

        if (empty($result))
        {
            return false;
        }

        $this->auth_data = $result;
        $prepared = $database->prepare('SELECT * FROM "' . USER_ROLES_TABLE . '" WHERE "user_id" = ?');
        $result = $database->executePreparedFetchAll($prepared, [$this->id()], PDO::FETCH_ASSOC, true);

        foreach ($result as $row)
        {
            $this->modifyRole($row['domain_id'], $row['role_id']);
        }

        return true;
    }

    public function writeToDatabase($temp_database = null)
    {
        if (empty($this->id()))
        {
            return false;
        }

        $database = (!is_null($temp_database)) ? $temp_database : $this->database;
        $prepared = $database->prepare('SELECT "entry" FROM "' . USERS_TABLE . '" WHERE "user_id" = ?');
        $result = $database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_COLUMN);

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

        $prepared->bindValue(':user_id', $this->authDataOrDefault('user_id', $this->id()), PDO::PARAM_STR);
        $prepared->bindValue(':display_name', $this->authDataOrDefault('display_name', null), PDO::PARAM_STR);
        $prepared->bindValue(':user_password', $this->authDataOrDefault('user_password', null), PDO::PARAM_STR);
        $prepared->bindValue(':active', $this->authDataOrDefault('active', 0), PDO::PARAM_INT);
        $prepared->bindValue(':super_admin', $this->authDataOrDefault('super_admin', 0), PDO::PARAM_INT);
        $prepared->bindValue(':last_login', $this->authDataOrDefault('last_login', 0), PDO::PARAM_INT);
        $database->executePrepared($prepared);

        foreach ($this->user_roles as $domain_id => $user_role)
        {
            $prepared = $database->prepare(
                    'SELECT "entry" FROM "' . USER_ROLES_TABLE . '" WHERE "user_id" = ? AND "domain_id" = ?');
            $result = $database->executePreparedFetch($prepared, [$this->id(), $domain_id], PDO::FETCH_COLUMN);

            if ($result)
            {
                $prepared = $database->prepare(
                        'UPDATE "' . USER_ROLES_TABLE .
                        '" SET "user_id" = :user_id, "role_id" = :role_id, "domain_id" = :domain_id WHERE "entry" = :entry');
                $prepared->bindValue(':entry', $result, PDO::PARAM_INT);
            }
            else
            {
                $prepared = $database->prepare(
                        'INSERT INTO "' . USER_ROLES_TABLE . '" ("user_id", "role_id", "domain_id") VALUES
                    (:user_id, :role_id, :domain_id)');
            }

            $prepared->bindValue(':user_id', $this->id(), PDO::PARAM_STR);
            $prepared->bindValue(':role_id', $user_role['role_id'], PDO::PARAM_STR);
            $prepared->bindValue(':domain_id', $domain_id, PDO::PARAM_STR);
            $database->executePrepared($prepared);
        }

        return true;
    }

    public function setupNew()
    {
    }

    public function remove()
    {
        $prepared = $this->database->prepare('DELETE FROM "' . USER_ROLES_TABLE . '" WHERE "user_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
        $prepared = $this->database->prepare('DELETE FROM "' . USERS_TABLE . '" WHERE "user_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function updatePassword(string $new_password)
    {
        $this->auth_data['user_password'] = nel_password_hash($new_password, NEL_PASSWORD_ALGORITHM);
    }

    public function checkRole(Domain $domain, bool $return_id = false)
    {
        if (!isset($this->user_roles[$domain->id()]))
        {
            return false;
        }

        if ($return_id)
        {
            return $this->user_roles[$domain->id()]['role_id'];
        }
        else
        {
            return $this->user_roles[$domain->id()]['role'];
        }
    }

    public function modifyRole($domain_id, $role_id)
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

    public function removeRole($domain_id, $role_id)
    {
        if (!isset($this->user_roles[$domain_id]))
        {
            return;
        }

        $prepared = $this->database->prepare(
                'DELETE FROM "' . USER_ROLES_TABLE . '" WHERE "user_id" = ? AND "domain_id" = ?');
        $this->database->executePrepared($prepared, [$this->id(), $domain_id]);
        unset($this->user_roles[$domain_id]);
    }

    public function checkPermission(Domain $domain, $perm_id)
    {
        // Super Admin can do all the things
        if ($this->isSuperAdmin())
        {
            return true;
        }

        $role = $this->checkRole($domain);

        if ($role && $role->checkPermission($perm_id))
        {
            return true;
        }

        // Check if there is a global variation which may have permission set
        $global_domain = $domain->globalVariation();

        if($global_domain)
        {
            $role = $this->checkRole($global_domain);

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
        return boolval($this->getInfo('active') || $this->isSuperAdmin());
    }

    public function isSuperAdmin()
    {
        return boolval($this->auth_data['super_admin']);
    }
}
