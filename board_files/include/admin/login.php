<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/main_panel.php';
require_once INCLUDE_PATH . 'output/management/login_page.php';

function nel_verify_login_or_session($dataforce)
{
    $authorize = nel_authorize();
    $dbh = nel_database();

    if (isset($dataforce['mode']) && $dataforce['mode'] === 'admin->login')
    {
        if ($dataforce['username'] !== '' &&
        nel_password_verify($dataforce['admin_pass'], $authorize->get_user_info($dataforce['username'], 'user_password')))
        {
            $dataforce['login_valid'] = true;
            $prepared = $dbh->prepare('DELETE FROM "' . LOGINS_TABLE . '" WHERE "ip" = ?');
            $dbh->executePrepared($prepared, array($_SERVER['REMOTE_ADDR']), true);
        }
        else
        {
            $prepared = $dbh->prepare('SELECT * FROM "' . LOGINS_TABLE . '" WHERE "ip" = ?');
            $result = $dbh->executePreparedFetch($prepared, array($_SERVER['REMOTE_ADDR']), PDO::FETCH_ASSOC, true);

            if ($result !== false)
            {
                $attempts = $result['failed_attempts'];
                $last_attempt = $result['last_attempt'];
                $remove = false;

                if ($result['failed_attempts'] < 2147483647)
                {
                    ++ $attempts;
                }

                if (time() - $result['last_attempt'] > 3600)
                {
                    $last_attempt = time();
                    $prepared = $dbh->prepare('DELETE FROM "' . LOGINS_TABLE . '" WHERE "ip" = ?');
                    $dbh->executePrepared($prepared, array($_SERVER['REMOTE_ADDR']), true);
                }

                if ($result['failed_attempts'] >= 5 && time() - $result['last_attempt'] > 15)
                {
                    $last_attempt = time();
                }

                $prepared = $dbh->prepare('UPDATE "' . LOGINS_TABLE .
                '" SET "failed_attempts" = ?, "last_attempt" = ? WHERE "ip" = ?');
                $dbh->executePrepared($prepared, array($attempts, $last_attempt, $_SERVER['REMOTE_ADDR']), true);
                nel_derp(0, nel_stext('ERROR_0')); // TODO: Create error for too many login attempts
            }
            else
            {
                $prepared = $dbh->prepare('INSERT INTO "' . LOGINS_TABLE .
                '" (ip, failed_attempts, last_attempt) VALUES (?, ?, ?)');
                $dbh->executePrepared($prepared, array($_SERVER['REMOTE_ADDR'], 1, time()), true);
            }

            nel_derp(0, nel_stext('ERROR_0')); // TODO: Create error for failed login
        }
    }

    nel_initialize_session($dataforce);
}

function nel_login($dataforce)
{
    if (!nel_session_is_ignored())
    {
        nel_generate_main_panel();
    }
    else
    {
        nel_insert_default_admin(); // Let's make sure there's some kind of admin in the system
        nel_insert_role_defaults(); // Also make sure the role exists
        nel_generate_login_page();
    }

    nel_clean_exit($dataforce, true);
}
