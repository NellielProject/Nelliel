<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/main_panel.php';
require_once INCLUDE_PATH . 'output/management/login_page.php';

function nel_verify_login()
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $database = nel_database();
    $login_valid = false;

    if (isset($_POST['username']) && $_POST['username'] !== '' && $authorization->userExists($_POST['username']))
    {
        $user = $authorization->getUser($_POST['username']);
        $user_login_fails = $user->auth_data['failed_logins'];
        $last_user_attempt = $user->auth_data['last_failed_login'];

        if ($user_login_fails > 10 && time() - $last_user_attempt < 300)
        {
            nel_derp(212,
                    _gettext(
                            'This account has had too many failed login attempts and has been temporarily locked for 10 minutes.'));
        }

        if ($user_login_fails > 20 && time() - $last_user_attempt < 1800)
        {
            nel_derp(213,
                    _gettext(
                            'This account has had too many failed login attempts and has been temporarily locked for 30 minutes.'));
        }

        if (nel_password_verify($_POST['super_sekrit'], $user->auth_data['user_password']))
        {
            $login_valid = true;
            $prepared = $database->prepare('DELETE FROM "' . LOGINS_TABLE . '" WHERE "ip_address" = ?');
            $database->executePrepared($prepared, array(@inet_pton($_SERVER['REMOTE_ADDR'])), true);
            $user_login_fails = 0;
            $attempt_time = 0;
        }
        else
        {
            $user_login_fails ++;
            $attempt_time = time();
        }

        $prepared = $database->prepare(
                'UPDATE "' . USER_TABLE . '" SET "failed_logins" = ?, "last_failed_login" = ? WHERE "user_id" = ?');
        $database->executePrepared($prepared, array($user_login_fails, $attempt_time, $_POST['username']), true);

        if (!$login_valid)
        {
            nel_derp(210, _gettext('You have failed to login. Please wait a few seconds before trying again.'));
        }
    }
    else
    {
        $prepared = $database->prepare('SELECT * FROM "' . LOGINS_TABLE . '" WHERE "ip_address" = ?');
        $result = $database->executePreparedFetch($prepared, array(@inet_pton($_SERVER['REMOTE_ADDR'])), PDO::FETCH_ASSOC,
                true);

        if ($result !== false && !empty($result))
        {
            $last_period = time() - $result['last_attempt'];
            $attempts = ($result['failed_attempts'] < 21472483647) ? $result['failed_attempts'] : 21472483647;

            if ($last_period > 3600)
            {
                $prepared = $database->prepare('DELETE FROM "' . LOGINS_TABLE . '" WHERE "ip_address" = ?');
                $database->executePrepared($prepared, array(@inet_pton($_SERVER['REMOTE_ADDR'])), true);
            }
            else if ($last_period > 5)
            {
                $attempts ++;
                $prepared = $database->prepare(
                        'UPDATE "' . LOGINS_TABLE .
                        '" SET "last_attempt" = ?, "failed_attempts" = ? WHERE "ip_address" = ?');
                $database->executePrepared($prepared, array(time(), $attempts, @inet_pton($_SERVER['REMOTE_ADDR'])), true);
            }
            else
            {
                nel_derp(211, _gettext('JFC! Slow down on the failure!'));
            }
        }
        else
        {
            $prepared = $database->prepare(
                    'INSERT INTO "' . LOGINS_TABLE . '" (ip_address, failed_attempts, last_attempt) VALUES (?, ?, ?)');
            $database->executePrepared($prepared, array(@inet_pton($_SERVER['REMOTE_ADDR']), 1, time()), true);
        }
    }

    return $login_valid;
}
