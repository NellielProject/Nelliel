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
        $form_username = strval($_POST['username'] ?? '');
        $session_username = strval($_SESSION['username'] ?? '');
        $form_password = strval($_POST['super_sekrit'] ?? '');
        $rate_limit = nel_utilities()->rateLimit();

        if ($rate_limit->lastTime($hashed_ip_address, 'login') > $attempt_time - 3) {
            $rate_limit->updateAttempts($hashed_ip_address, 'login');
            nel_derp(203, _gettext('Detecting rapid login attempts. Wait a few seconds.'));
        }

        if (empty($form_username)) {
            $rate_limit->updateAttempts($hashed_ip_address, 'login');
            nel_derp(200, _gettext('No user ID provided.'));
        }

        if (empty($form_password)) {
            $rate_limit->updateAttempts($hashed_ip_address, 'login');
            nel_derp(201, _gettext('No password provided.'));
        }

        $user = $this->authorization->getUser($form_username);
        $valid_user = false;
        $valid_password = false;

        if (!$user->empty()) {
            $valid_password = nel_password_verify($form_password, $user->getData('password'));

            if (empty($session_username)) {
                $valid_user = true;
            } else {
                $valid_user = $session_username === utf8_strtolower($form_username);
            }
        }

        if (!$valid_user || !$valid_password) {
            $rate_limit->updateAttempts($hashed_ip_address, 'login');
            nel_derp(202, _gettext('Username or password is incorrect.'));
        }

        $rate_limit->clearAttempts($hashed_ip_address, 'login', true);
        $user->changeData('last_login', $attempt_time);
        nel_logger('system')->info('Sucessfully logged in.', ['event' => 'LOGIN', 'username' => $user->id()]);
        return $user;
    }
}
