<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_get_authorization()
{
    static $authorized;

    if (!isset($authorized))
    {
        $authorized = new nel_authorization();
    }

    return $authorized;
}

//
// Handle all the authorization functions
//
class nel_authorization
{
    private $authorized = array();
    private $dbh;
    private $staff = array();

    function __construct()
    {
        // We need a check if this exists, etc.
        //include BOARD_FILES . 'auth_data.nel.php';
        //$this->authorized = $authorized;
        $this->dbh = nel_get_db_handle();
    }

    private function user_exists($user)
    {
        return array_key_exists($user, $this->authorized);
    }

    private function get_user_data($user)
    {
        $result = $this->dbh->query('SELECT * FROM "' . USER_TABLE . '" WHERE "user_id" = \'' . $user . '\';');
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    private function get_role_data($role)
    {
        $result = $this->dbh->query('SELECT * FROM "' . ROLES_TABLE . '" WHERE "role_id" = \'' . $role . '\';');
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function set_up_user($user)
    {
        $info = array();
        $user_data = $this->get_user_data($user);
        $role_data = $this->get_role_data($user_data['role_id']);

        foreach ($user_data as $key => $value)
        {
            if ($key != 'role_id')
            {
                $info['info'][$key] = $value;
            }
        }

        foreach ($role_data as $key => $value)
        {
            if (substr($key, 0, 5) === 'perm_')
            {
                $info['perms'][$key] = ($value) ? true : false;
            }
            else
            {
                $info['role'][$key] = $value;
            }
        }

        $info['changed'] = false;
        $this->authorized[$user] = $info;
    }

    public function get_user($user)
    {
        if ($this->user_exists($user))
        {
            return $this->authorized[$user];
        }

        return false;
    }

    public function get_user_info($user, $info)
    {
        if ($this->user_exists($user))
        {
            return $this->authorized[$user]['info'][$info];
        }

        return false;
    }

    public function get_user_role($user, $role)
    {
        if ($this->user_exists($user))
        {
            return $this->authorized[$user]['role'][$role];
        }

        return false;
    }

    public function get_user_perm($user, $perm)
    {
        if ($this->user_exists($user) && is_bool($this->authorized[$user]['perms'][$perm]))
        {
            return $this->authorized[$user]['perms'][$perm];
        }

        return false;
    }

    public function update_user($user, $update)
    {
        $this->authorized[$user] = $update;
        $this->user_updated($user);
    }

    public function update_user_info($user, $info, $update)
    {
        $this->authorized[$user][$info] = $update;
        $this->user_updated($user);
    }

    public function update_user_role($user, $role, $update)
    {
        $this->authorized[$user]['role'][$role] = $update;
        $this->user_updated($user);
    }

    public function update_user_perm($user, $perm, $update)
    {
        $this->authorized[$user]['perms'][$perm] = $update;
        $this->user_updated($user);
    }

    public function remove_user($user)
    {
        if ($this->user_exists($user))
        {
            $this->dbh->query('DELETE FROM "' . USER_TABLE . '" WHERE "user_id" = \'' .
                 $this->authorized[$user]['user_id'] . '\';');
            $this->dbh->query('DELETE FROM "' . ROLE_TABLE . '" WHERE "role_id" = \'' .
                 $this->authorized[$user]['role']['role_id'] . '\';');
            unset($this->authorized[$user]);
            return true;
        }

        return false;
    }

    private function user_updated($user)
    {
        $this->authorized[$user]['changed'] = true;
    }

    private function save_user($user)
    {
        $user_data = $this->authorized[$user];

        if (!$user_data['changed'])
        {
            return;
        }

        $update_user = '';
        $update_role = '';
        $bind_user = '';
        $bind_role = '';

        foreach ($user_data as $key => $value)
        {
            if ($isset($key) && $key !== 'changed')
            {
                if($key === 'role' || $key === 'perms')
                {
                    foreach ($key as $key2 => $value2)
                    {
                        $update_role .= '"' . $key2 . '" = :' . $key2 . ', ';
                        $bind_role[$key2] = $value2;
                    }
                }
                else
                {
                    $update_user .= '"' . $key . '" = :' . $key . ', ';
                    $bind_user[':' . $key] = $value;
                }
            }
        }

        $prepared = $this->dbh->prepare('UPDATE "' . USER_TABLE . '" WHERE "user_id" = \'' .
             $this->authorized[$user]['user_id'] . '\' SET ' . $update_user . ';');

        foreach ($bind_user as $key => $value)
        {
            $prepared->bindValue($key, $value);
        }

        $prepared->execute();

        $prepared = $this->dbh->prepare('UPDATE "' . ROLES_TABLE . '" WHERE "role_id" = \'' .
        $this->authorized[$user]['role']['role_id'] . '\' SET ' . $update_role . ';');

        foreach ($bind_role as $key => $value)
        {
            $prepared->bindValue($key, $value);
        }

        $prepared->execute();
    }
}

