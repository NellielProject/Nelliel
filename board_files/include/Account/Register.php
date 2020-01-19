<?php

namespace Nelliel\Account;

use Nelliel\NellielPDO;
use Nelliel\Auth\Authorization;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Register
{
    private $authorization;
    private $database;

    function __construct(Authorization $authorization, NellielPDO $database)
    {
        $this->authorization = $authorization;
        $this->database = $database;
    }

    public function new()
    {
        $register_user_id = (isset($_POST['register_user_id'])) ? strval($_POST['register_user_id']) : '';
        $register_password = (isset($_POST['register_super_sekrit'])) ? strval($_POST['register_super_sekrit']) : '';
        $register_password_confirm = (isset($_POST['register_super_sekrit_confirm'])) ? strval(
                $_POST['register_super_sekrit_confirm']) : '';

        if (empty($register_user_id))
        {
            nel_derp(210, _gettext('No user ID provided.'));
        }

        if (empty($register_password))
        {
            nel_derp(211, _gettext('No password provided.'));
        }

        if ($this->authorization->userExists($register_user_id))
        {
            nel_derp(212, _gettext('User already exists.'));
        }

        if (!hash_equals($register_password, $register_password_confirm))
        {
            nel_derp(213, _gettext('Passwords do not match.'));
        }

        $new_user = $this->authorization->newUser($register_user_id, $register_password);
        $new_user->updatePassword($register_password);
        $new_user->changeOrAddRole('', 'BASIC');
        $new_user->auth_data['active'] = 1;
        $new_user->auth_data['display_name'] = $register_user_id;
        $this->authorization->saveUsers();
        // Successful
    }
}
