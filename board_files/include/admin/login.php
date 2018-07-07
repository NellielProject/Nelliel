<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/main_panel.php';
require_once INCLUDE_PATH . 'output/management/login_page.php';

function nel_verify_login_or_session($manage, $action)
{
    $authorize = nel_authorize();
    $dbh = nel_database();
    $login_valid = false;

    if ($manage === 'login' && !is_null($action))
    {
        if ($_POST['username'] !== '' && $authorize->userExists($_POST['username']))
        {
            $user_login_fails = $authorize->getUserInfo($_POST['username'], 'failed_logins');
            $last_user_attempt = $authorize->getUserInfo($_POST['username'], 'last_failed_login');

            if ($user_login_fails > 10 && time() - $last_user_attempt < 300)
            {
                nel_derp(302, _gettext('This account has had too many failed login attempts and has been temporarily locked for 10 minutes.'));
            }

            if ($user_login_fails > 20 && time() - $last_user_attempt < 1800)
            {
                nel_derp(303, _gettext('This account has had too many failed login attempts and has been temporarily locked for 30 minutes.'));
            }

            if (nel_password_verify($_POST['super_sekrit'], $authorize->getUserInfo($_POST['username'], 'user_password')))
            {
                $login_valid = true;
                $prepared = $dbh->prepare('DELETE FROM "' . LOGINS_TABLE . '" WHERE "ip_address" = ?');
                $dbh->executePrepared($prepared, array(@inet_pton($_SERVER['REMOTE_ADDR'])), true);
                $user_login_fails = 0;
                $attempt_time = 0;
            }
            else
            {
                $user_login_fails ++;
                $attempt_time = time();
            }

            $prepared = $dbh->prepare('UPDATE "' . USER_TABLE .
                 '" SET "failed_logins" = ?, "last_failed_login" = ? WHERE "user_id" = ?');
            $dbh->executePrepared($prepared, array($user_login_fails, $attempt_time, $_POST['username']), true);

            if (!$login_valid)
            {
                nel_derp(300, _gettext('You have failed to login. Please wait a few seconds before trying again.'));
            }
        }
        else
        {
            $prepared = $dbh->prepare('SELECT * FROM "' . LOGINS_TABLE . '" WHERE "ip_address" = ? LIMIT 1');
            $result = $dbh->executePreparedFetch($prepared, array(@inet_pton($_SERVER['REMOTE_ADDR'])), PDO::FETCH_ASSOC, true);

            if ($result !== false && !empty($result))
            {
                $last_period = time() - $result['last_attempt'];
                $attempts = ($result['failed_attempts'] < 21472483647) ? $result['failed_attempts'] : 21472483647;

                if ($last_period > 3600)
                {
                    $prepared = $dbh->prepare('DELETE FROM "' . LOGINS_TABLE . '" WHERE "ip_address" = ?');
                    $dbh->executePrepared($prepared, array(@inet_pton($_SERVER['REMOTE_ADDR'])), true);
                }
                else if ($last_period > 5)
                {
                    $attempts ++;
                    $prepared = $dbh->prepare('UPDATE "' . LOGINS_TABLE .
                         '" SET "last_attempt" = ?, "failed_attempts" = ? WHERE "ip_address" = ?');
                    $dbh->executePrepared($prepared, array(time(), $attempts, @inet_pton($_SERVER['REMOTE_ADDR'])), true);
                }
                else
                {
                    nel_derp(301, _gettext('JFC! Slow down on the failure!'));
                }
            }
            else
            {
                $prepared = $dbh->prepare('INSERT INTO "' . LOGINS_TABLE .
                     '" (ip_address, failed_attempts, last_attempt) VALUES (?, ?, ?)');
                $dbh->executePrepared($prepared, array(@inet_pton($_SERVER['REMOTE_ADDR']), 1, time()), true);
            }
        }
    }

    nel_sessions()->initializeSession($manage, $action, $login_valid);
}

function nel_login()
{
    if (!nel_sessions()->sessionIsIgnored())
    {
        if(INPUT_BOARD_ID === '')
        {
            nel_render_main_panel();
        }
        else
        {
            nel_render_main_board_panel(INPUT_BOARD_ID);
        }
    }
    else
    {
        nel_render_login_page();
    }

    nel_clean_exit();
}
