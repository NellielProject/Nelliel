<?php
declare(strict_types = 1);

namespace Nelliel\Account;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\AntiSpam\CAPTCHA;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputRegisterPage;

class Register
{
    private $authorization;
    private $database;
    private $domain;

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->database = $this->domain->database();
    }

    public function new(): void
    {
        $captcha = new CAPTCHA($this->domain);

        if (nel_get_cached_domain(Domain::SITE)->setting('enable_captchas') && $this->domain->setting('use_register_captcha')) {
            $captcha_key = $_COOKIE['captcha-key'] ?? '';
            $captcha_answer = $_POST['new_post']['captcha_answer'] ?? '';
            $captcha->verify($captcha_key, $captcha_answer);
        }

        $register_username = (isset($_POST['register_username'])) ? strval($_POST['register_username']) : '';
        $register_password = (isset($_POST['register_super_sekrit'])) ? strval($_POST['register_super_sekrit']) : '';
        $register_password_confirm = (isset($_POST['register_super_sekrit_confirm'])) ? strval(
            $_POST['register_super_sekrit_confirm']) : '';
        $creating_owner = isset($_POST['create_owner']);

        if ($creating_owner) {
            $given_id = $_POST['create_owner'];
            $install_id = '';

            if (file_exists(NEL_GENERATED_FILES_PATH . 'create_owner.php')) {
                include NEL_GENERATED_FILES_PATH . 'create_owner.php';
            }

            if ($install_id != $given_id) {
                nel_derp(214, _gettext('Site owner cannot be created. Install ID does not match.'));
            }
        }

        if (empty($register_username)) {
            nel_derp(210, _gettext('No user ID provided.'), 401);
        }

        if (empty($register_password)) {
            nel_derp(211, _gettext('No password provided.'), 401);
        }

        if (utf8_strlen($register_username) > 50) {
            nel_derp(216, sprintf(_gettext('Username is too long. Maximum %d characters.'), 50), 401);
        }

        if (utf8_strlen($register_password) > nel_crypt_config()->configValue('account_password_max_length')) {
            nel_derp(217,
                sprintf(_gettext('Password is too long. Maximum %d characters.'),
                    nel_crypt_config()->configValue('account_password_max_length')), 401);
        }

        if ($this->authorization->userExists($register_username)) {
            nel_derp(212, _gettext('User already exists.'));
        }

        if (!hash_equals($register_password, $register_password_confirm)) {
            nel_derp(213, _gettext('Passwords do not match.'), 401);
        }

        $new_user = $this->authorization->getUser($register_username);
        $new_user->changeData('display_name', $register_username);
        $new_user->updatePassword($register_password);

        if ($creating_owner) {
            $new_user->changeData('owner', 1);
        } else {
            $new_user->modifyRole(Domain::SITE, 'basic_user');
            $new_user->changeData('owner', 0);
        }

        $new_user->changeData('active', 1);
        $this->authorization->saveUsers();
        nel_logger('system')->info('New user registered.', ['event' => 'user_registered', 'username' => $new_user->id()]);

        // Successful

        if ($creating_owner) {
            unlink(NEL_GENERATED_FILES_PATH . 'create_owner.php');
        }

        $output_register = new OutputRegisterPage($this->domain, false);
        $output_register->render(['section' => 'registration-done'], false);
    }
}
