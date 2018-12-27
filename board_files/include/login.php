<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/main_panel.php';
require_once INCLUDE_PATH . 'output/management/login_page.php';

function nel_verify_login()
{
    $database = nel_database();
    $authorization = new \Nelliel\Auth\Authorization($database);
    $login_valid = false;
    $delay_seconds = 3;
    $attempt_time = time();
    $cleanup_time = $attempt_time - $delay_seconds;

    if (isset($_POST['username']) && $_POST['username'] !== '' && $authorization->userExists($_POST['username']))
    {
        $user = $authorization->getUser($_POST['username']);

        if (isset($_POST['super_sekrit']) &&
                nel_password_verify($_POST['super_sekrit'], $user->auth_data['user_password']))
        {
            $login_valid = true;
            $prepared = $database->prepare('DELETE FROM "' . LOGINS_TABLE . '" WHERE "ip_address" = ?');
            $database->executePrepared($prepared, [@inet_pton($_SERVER['REMOTE_ADDR'])], true);
            nel_clear_old_login_attempts($database, $cleanup_time);
        }
    }

    if (!$login_valid)
    {
        $prepared = $database->prepare('SELECT "last_attempt" FROM "' . LOGINS_TABLE . '" WHERE "ip_address" = ?');
        $result = $database->executePreparedFetch($prepared, [@inet_pton($_SERVER['REMOTE_ADDR'])], PDO::FETCH_ASSOC,
                true);

        if (!empty($result))
        {
            if (($attempt_time - $result['last_attempt']) > $delay_seconds)
            {
                $prepared = $database->prepare(
                        'UPDATE "' . LOGINS_TABLE . '" SET "last_attempt" = ? WHERE "ip_address" = ?');
                $database->executePrepared($prepared, [$attempt_time, @inet_pton($_SERVER['REMOTE_ADDR'])], true);
                nel_clear_old_login_attempts($database, $cleanup_time);
            }
        }
        else
        {
            $prepared = $database->prepare(
                    'INSERT INTO "' . LOGINS_TABLE . '" (ip_address, last_attempt) VALUES (?, ?)');
            $database->executePrepared($prepared, [@inet_pton($_SERVER['REMOTE_ADDR']), $attempt_time], true);
            nel_clear_old_login_attempts($database, $cleanup_time);
        }

        nel_derp(210, _gettext('You have failed at login. Please wait a short time before trying again.'));
    }

    return $login_valid;
}

function nel_clear_old_login_attempts($database, $cleanup_time)
{
    $prepared = $database->prepare('DELETE FROM "' . LOGINS_TABLE . '" WHERE "last_attempt" > ?');
    $database->executePrepared($prepared, [$cleanup_time], true);
}
