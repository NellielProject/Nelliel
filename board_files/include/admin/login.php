<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/main_panel.php';
require_once INCLUDE_PATH . 'output/management/login_page.php';

function nel_verify_login_or_session($manage, $action, $dataforce)
{
    $authorize = nel_authorize();
    $dbh = nel_database();

    if ($manage === 'login' && !is_null($action))
    {
        if ($_POST['username'] !== '' && $authorize->user_exists($_POST['username']))
        {
            $user_login_fails = $authorize->get_user_info($_POST['username'], 'failed_logins');
            $last_user_attempt = $authorize->get_user_info($_POST['username'], 'last_failed_login');

            if ($user_login_fails > 10 && time() - $last_user_attempt < 300)
            {
                nel_derp(302, nel_stext('ERROR_302'));
            }

            if ($user_login_fails > 20 && time() - $last_user_attempt < 1800)
            {
                nel_derp(303, nel_stext('ERROR_303'));
            }

            if (nel_password_verify($_POST['super_sekrit'], $authorize->get_user_info($_POST['username'], 'user_password')))
            {
                $dataforce['login_valid'] = true;
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

            if (!$dataforce['login_valid'])
            {
                nel_derp(300, nel_stext('ERROR_300'));
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
                    nel_derp(301, nel_stext('ERROR_301'));
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

    nel_initialize_session($manage, $action, $dataforce);
}

function nel_login($dataforce)
{
    if (!nel_session_is_ignored())
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
        nel_insert_default_admin(); // Let's make sure there's some kind of admin in the system
        nel_insert_default_admin_role(); // And then be sure admin is assigned the role
        nel_render_login_page();
    }

    nel_clean_exit($dataforce, true);
}
