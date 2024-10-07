<?php
declare(strict_types = 1);

namespace Nelliel\Account;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\IPInfo;
use Nelliel\ReturnLink;
use Nelliel\AntiSpam\CAPTCHA;
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

    public function validate(): \Nelliel\Account\User
    {
        $captcha = new CAPTCHA($this->domain);

        if (nel_get_cached_domain(Domain::SITE)->setting('enable_captchas') && $this->domain->setting('use_login_captcha')) {
            $captcha_key = $_COOKIE['captcha-key'] ?? '';
            $captcha_answer = $_POST['new_post']['captcha_answer'] ?? '';
            $captcha->verify($captcha_key, $captcha_answer);
        }

        $return_url = nel_build_router_url([Domain::SITE, 'account', 'login']);
        $return_link = new ReturnLink($return_url, __('Return to login page'));
        $error_context = ['return_link' => $return_link];

        $attempt_time = time();
        $ip_info = new IPInfo(nel_request_ip_address());
        $form_username = strval($_POST['username'] ?? '');
        $session_username = strval($_SESSION['username'] ?? '');
        $form_password = strval($_POST['super_sekrit'] ?? '');
        $rate_limit = nel_utilities()->rateLimit();

        if ($rate_limit->lastTime($ip_info->getInfo('hashed_ip_address'), 'login') > $attempt_time - 3) {
            $rate_limit->updateAttempts($ip_info->getInfo('hashed_ip_address'), 'login');
            nel_derp(203, _gettext('Detecting rapid login attempts. Wait a few seconds.'), $error_context);
        }

        if (empty($form_username)) {
            $rate_limit->updateAttempts($ip_info->getInfo('hashed_ip_address'), 'login');
            nel_derp(200, _gettext('No user ID provided.'), 401, $error_context);
        }

        if (empty($form_password)) {
            $rate_limit->updateAttempts($ip_info->getInfo('hashed_ip_address'), 'login');
            nel_derp(201, _gettext('No password provided.'), 401, $error_context);
        }

        if (utf8_strlen($form_password) > nel_crypt_config()->configValue('account_password_max_length')) {
            nel_derp(204,
                sprintf(_gettext('Password is too long. Maximum %d characters.'),
                    nel_crypt_config()->configValue('account_password_max_length')), 401, $error_context);
        }

        $user = $this->authorization->getUser($form_username);
        $valid_user = false;
        $valid_password = false;

        if ($user->exists()) {
            if (utf8_strlen($form_password) <= nel_crypt_config()->configValue('account_password_max_length')) {
                $valid_password = nel_password_verify($form_password, $user->getData('password'));
            }

            if (empty($session_username)) {
                $valid_user = true;
            } else {
                $valid_user = $session_username === utf8_strtolower($form_username);
            }
        }

        if (!$valid_user || !$valid_password) {
            $rate_limit->updateAttempts($ip_info->getInfo('hashed_ip_address'), 'login');
            nel_derp(202, _gettext('Username or password is incorrect.'), 401, $error_context);
        }

        $rate_limit->clearAttempts($ip_info->getInfo('hashed_ip_address'), 'login', true);
        $user->changeData('last_login', $attempt_time);
        nel_logger('system')->info('Logged in.', ['event' => 'user_login', 'username' => $user->id()]);
        return $user;
    }
}
