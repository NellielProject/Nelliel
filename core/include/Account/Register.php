<?php
declare(strict_types = 1);

namespace Nelliel\Account;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

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

    public function new()
    {
        $captcha = new \Nelliel\AntiSpam\CAPTCHA($this->domain);

        if ($this->domain->setting('use_register_captcha')) {
            $captcha_key = $_COOKIE['captcha-key'] ?? '';
            $captcha_answer = $_POST['new_post']['captcha_answer'] ?? '';
            $captcha->verify($captcha_key, $captcha_answer);
        }

        if ($this->domain->setting('use_register_recaptcha')) {
            $captcha->verifyReCAPTCHA();
        }

        $register_user_id = (isset($_POST['register_user_id'])) ? strval($_POST['register_user_id']) : '';
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

        if (empty($register_user_id)) {
            nel_derp(210, _gettext('No user ID provided.'));
        }

        if (empty($register_password)) {
            nel_derp(211, _gettext('No password provided.'));
        }

        if ($this->authorization->userExists($register_user_id)) {
            nel_derp(212, _gettext('User already exists.'));
        }

        if (!hash_equals($register_password, $register_password_confirm)) {
            nel_derp(213, _gettext('Passwords do not match.'));
        }

        $new_user = $this->authorization->newUser($register_user_id);
        $new_user->updatePassword($register_password);

        if ($creating_owner) {
            $new_user->changeData('owner', 1);
        } else {
            $new_user->modifyRole(Domain::SITE, 'BASIC_USER');
            $new_user->changeData('owner', 0);
        }

        $new_user->changeData('active', 1);
        $new_user->changeData('display_name', $register_user_id);
        $this->authorization->saveUsers();

        // Successful

        if ($creating_owner) {
            unlink(NEL_GENERATED_FILES_PATH . 'create_owner.php');
        }

        $output_register = new \Nelliel\Output\OutputRegisterPage($this->domain, false);
        $output_register->render(['section' => 'registration-done'], false);
    }
}
