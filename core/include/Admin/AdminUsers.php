<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelUsers;

class AdminUsers extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_USERS_TABLE;
        $this->id_column = 'username';
        $this->panel_name = _gettext('Users');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_view_users');
        $output_panel = new OutputPanelUsers($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_users');
        $output_panel = new OutputPanelUsers($this->domain, false);
        $output_panel->new([], false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_users');
        $username = utf8_strtolower($_POST['username']);
        $this->update($username);
    }

    public function editor(string $username): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_users');
        $output_panel = new OutputPanelUsers($this->domain, false);
        $output_panel->edit(['username' => $username], false);
    }

    public function update(string $username): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_users');
        $update_user = $this->authorization->getUser($username);

        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                $value = nel_form_input_default($value);
            }

            if (strpos($key, 'domain_role') !== false) {
                $domain = Domain::getDomainFromID(utf8_substr($key, 12), $this->database);
                $update_user->modifyRole($domain->id(), $value);
                continue;
            }

            if ($key === 'user_password') {
                if (!empty($value)) {
                    $update_user->changeData('password', nel_password_hash($value, NEL_PASSWORD_ALGORITHM));
                }

                continue;
            }

            $update_user->changeData($key, $value);
        }

        $this->authorization->saveUsers();
        $this->panel();
    }

    public function delete(string $username): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_users');
        $this->authorization->removeUser($username);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_view_users':
                nel_derp(395, _gettext('You are not allowed to view users.'));
                break;

            case 'perm_manage_users':
                nel_derp(396, _gettext('You are not allowed to manage users.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
