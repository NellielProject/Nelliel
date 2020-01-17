<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_register_account()
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $register_username = $_POST['register_username'] ?? '';
    $register_password = $_POST['register_super_sekrit'] ?? '';

    if (empty($register_username))
    {
        // Did not enter username
    }

    if ($authorization->userExists($register_username))
    {
        // User exists
    }

    if(empty($register_password))
    {
        // Empty password
    }

    if(!hash_equals($register_password, $_POST['register_super_sekrit_confirm']))
    {
        // Password not match
    }

    $new_user = $authorization->newUser($register_username, $register_password);
    $new_user->updatePassword($register_password);
    $new_user->changeOrAddRole('', 'BASIC');
    $new_user->auth_data['active'] = 1;
    $new_user->auth_data['display_name'] = $register_username;
    $authorization->saveUsers();
    // Successful
}
