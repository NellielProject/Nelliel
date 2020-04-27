<?php

namespace Nelliel\Account;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\NellielPDO;
use Nelliel\Auth\Authorization;
use PDO;

class Login
{
    private $authorization;
    private $database;

    function __construct(Authorization $authorization, NellielPDO $database)
    {
        $this->authorization = $authorization;
        $this->database = $database;
    }

    public function validate()
    {
        $login_data = array();
        $attempt_time = time();
        $form_user_id = (isset($_POST['user_id'])) ? strval($_POST['user_id']) : '';
        $session_user_id = (isset($_SESSION['user_id'])) ? strval($_SESSION['user_id']) : '';
        $form_password = (isset($_POST['super_sekrit'])) ? strval($_POST['super_sekrit']) : '';

        if (empty($form_user_id))
        {
            $this->updateAttempts($attempt_time);
            nel_derp(200, _gettext('No user ID provided.'));
        }

        if (empty($form_password))
        {
            $this->updateAttempts($attempt_time);
            nel_derp(201, _gettext('No password provided.'));
        }

        $user = $this->authorization->getUser($form_user_id);
        $valid_user = false;
        $valid_password = false;

        if ($user)
        {
            if (isset($user->auth_data['user_password']))
            {
                $valid_password = nel_password_verify($form_password, $user->auth_data['user_password']);
            }

            if (empty($session_user_id))
            {
                $valid_user = true;
            }
            else
            {
                $valid_user = $session_user_id === $form_user_id;
            }
        }

        if (!$valid_user || !$valid_password)
        {
            $this->updateAttempts($attempt_time);
            nel_derp(202, _gettext('User ID or password is incorrect.'));
        }

        $login_data['user_id'] = $form_user_id;
        $login_data['login_time'] = $attempt_time;

        $prepared = $this->database->prepare(
                'DELETE FROM "' . LOGIN_ATTEMPTS_TABLE . '" WHERE "ip_address" = :ip_address');
        $prepared->bindValue(':ip_address', @inet_pton($_SERVER['REMOTE_ADDR']), PDO::PARAM_LOB);
        $this->database->executePrepared($prepared, null, true);
        $prepared = $this->database->prepare('UPDATE "' . USERS_TABLE . '" SET "last_login" = ? WHERE "user_id" = ?');
        $this->database->executePrepared($prepared, [time(), $_POST['user_id']]);
        return $login_data;
    }

    public function updateAttempts(int $attempt_time)
    {
        $delay_seconds = 3;
        $prepared = $this->database->prepare(
                'SELECT "last_attempt" FROM "' . LOGIN_ATTEMPTS_TABLE . '" WHERE "ip_address" = :ip_address');
        $prepared->bindValue(':ip_address', @inet_pton($_SERVER['REMOTE_ADDR']), PDO::PARAM_LOB);
        $result = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC, true);

        if (!empty($result))
        {
            if (($attempt_time - $result['last_attempt']) > $delay_seconds)
            {
                $prepared = $this->database->prepare(
                        'UPDATE "' . LOGIN_ATTEMPTS_TABLE .
                        '" SET "last_attempt" = :last_attempt WHERE "ip_address" = :ip_address');
                $prepared->bindValue(':last_attempt', $attempt_time, PDO::PARAM_INT);
                $prepared->bindValue(':ip_address', @inet_pton($_SERVER['REMOTE_ADDR']), PDO::PARAM_LOB);
                $this->database->executePrepared($prepared, null, true);
            }
        }
        else
        {
            $key = sha1($_SERVER['REMOTE_ADDR'] . random_bytes(16));
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . LOGIN_ATTEMPTS_TABLE .
                    '" (key, ip_address, last_attempt) VALUES (:key, :ip_address, :last_attempt)');
            $prepared->bindValue(':key', $key, PDO::PARAM_STR);
            $prepared->bindValue(':ip_address', @inet_pton($_SERVER['REMOTE_ADDR']), PDO::PARAM_LOB);
            $prepared->bindValue(':last_attempt', $attempt_time, PDO::PARAM_INT);
            $this->database->executePrepared($prepared, null, true);
        }
    }

    public function cleanupAttempts(int $cleanup_time)
    {
        $prepared = $this->database->prepare('DELETE FROM "' . LOGIN_ATTEMPTS_TABLE . '" WHERE "last_attempt" > ?');
        $this->database->executePrepared($prepared, [$cleanup_time], true);
    }
}
