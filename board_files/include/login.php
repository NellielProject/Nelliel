<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/main_panel.php';

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
            $prepared = $database->prepare(
                    'DELETE FROM "' . LOGIN_ATTEMPTS_TABLE . '" WHERE "ip_address" = :ip_address');
            $prepared->bindValue(':ip_address', @inet_pton($_SERVER['REMOTE_ADDR']), PDO::PARAM_LOB);
            $database->executePrepared($prepared, null, true);
            nel_clear_old_login_attempts($database, $cleanup_time);
        }
    }

    if (!$login_valid)
    {
        $prepared = $database->prepare(
                'SELECT "last_attempt" FROM "' . LOGIN_ATTEMPTS_TABLE . '" WHERE "ip_address" = :ip_address');
        $prepared->bindValue(':ip_address', @inet_pton($_SERVER['REMOTE_ADDR']), PDO::PARAM_LOB);
        $result = $database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC, true);

        if (!empty($result))
        {
            if (($attempt_time - $result['last_attempt']) > $delay_seconds)
            {
                $prepared = $database->prepare(
                        'UPDATE "' . LOGIN_ATTEMPTS_TABLE .
                        '" SET "last_attempt" = :last_attempt WHERE "ip_address" = :ip_address');
                $prepared->bindValue(':last_attempt', $attempt_time, PDO::PARAM_INT);
                $prepared->bindValue(':ip_address', @inet_pton($_SERVER['REMOTE_ADDR']), PDO::PARAM_LOB);
                $database->executePrepared($prepared, null, true);
                nel_clear_old_login_attempts($database, $cleanup_time);
            }
        }
        else
        {
            $prepared = $database->prepare(
                    'INSERT INTO "' . LOGIN_ATTEMPTS_TABLE .
                    '" (ip_address, last_attempt) VALUES (:ip_address, :last_attempt)');
            $prepared->bindValue(':ip_address', @inet_pton($_SERVER['REMOTE_ADDR']), PDO::PARAM_LOB);
            $prepared->bindValue(':last_attempt', $attempt_time, PDO::PARAM_INT);
            $database->executePrepared($prepared, null, true);
            nel_clear_old_login_attempts($database, $cleanup_time);
        }

        nel_derp(210, _gettext('You have failed at login. Please wait a short time before trying again.'));
    }

    $prepared = $database->prepare('UPDATE "' . USERS_TABLE . '" SET "last_login" = ? WHERE "user_id" = ?');
    $database->executePrepared($prepared, [$attempt_time, $_POST['username']]);
    return $login_valid;
}

function nel_clear_old_login_attempts($database, $cleanup_time)
{
    $prepared = $database->prepare('DELETE FROM "' . LOGIN_ATTEMPTS_TABLE . '" WHERE "last_attempt" > ?');
    $database->executePrepared($prepared, [$cleanup_time], true);
}
