<?php
declare(strict_types = 1);

namespace Nelliel\Account;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Auth\AuthUser;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class Login
{
    private $authorization;
    private $database;
    private $domain;

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->database = $domain->database();
    }

    public function validate(): AuthUser
    {
        $captcha = new \Nelliel\AntiSpam\CAPTCHA($this->domain);

        if ($this->domain->setting('use_login_captcha')) {
            $captcha_key = $_COOKIE['captcha-key'] ?? '';
            $captcha_answer = $_POST['new_post']['captcha_answer'] ?? '';
            $captcha->verify($captcha_key, $captcha_answer);
        }

        if ($this->domain->setting('use_login_recaptcha')) {
            $captcha->verifyReCAPTCHA();
        }

        $attempt_time = time();
        $hashed_ip_address = nel_request_ip_address(true);
        $form_user_id = strval($_POST['user_id'] ?? '');
        $session_user_id = strval($_SESSION['user_id'] ?? '');
        $form_password = strval($_POST['super_sekrit'] ?? '');
        $rate_limit = nel_utilities()->rateLimit();

        if ($rate_limit->lastTime($hashed_ip_address, 'login') > $attempt_time - 3) {
            $rate_limit->updateAttempts($hashed_ip_address, 'login');
            nel_derp(203, _gettext('Detecting rapid login attempts. Wait a few seconds.'));
        }

        if (empty($form_user_id)) {
            $rate_limit->updateAttempts($hashed_ip_address, 'login');
            nel_derp(200, _gettext('No user ID provided.'));
        }

        if (empty($form_password)) {
            $rate_limit->updateAttempts($hashed_ip_address, 'login');
            nel_derp(201, _gettext('No password provided.'));
        }

        $user = $this->authorization->getUser($form_user_id);
        $valid_user = false;
        $valid_password = false;

        if (!$user->empty()) {
            $valid_password = nel_password_verify($form_password, $user->getData('user_password'));

            if (empty($session_user_id)) {
                $valid_user = true;
            } else {
                $valid_user = $session_user_id === $form_user_id;
            }
        }

        if (!$valid_user || !$valid_password) {
            $rate_limit->updateAttempts($hashed_ip_address, 'login');
            nel_derp(202, _gettext('User ID or password is incorrect.'));
        }

        $rate_limit->clearAttempts($hashed_ip_address, 'login', true);
        $user->changeData('last_login', $attempt_time);
        nel_logger('system')->info('Sucessfully logged in.', ['event' => 'LOGIN', 'user_id' => $user->id()]);
        return $user;
    }
}
