<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Domains\DomainGlobal;
use Nelliel\Domains\DomainSite;
use Nelliel\Output\OutputPanelUsers;

class AdminUsers extends Admin
{
    private $username;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->username = $_GET['username'] ?? null;
        $this->data_table = NEL_USERS_TABLE;
        $this->id_field = 'username';
        $this->id_column = 'username';
        $this->panel_name = _gettext('Users');

        if (!is_null($this->username) && !$this->authorization->userExists($this->username)) {
            nel_derp(230, _gettext('The specified user does not exist.'));
        }
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_users_view');
        $output_panel = new OutputPanelUsers($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_users_manage');
        $output_panel = new OutputPanelUsers($this->domain, false);
        $output_panel->new(['username' => $this->username], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_users_manage');
        $this->username = $_POST['username'];
        $this->update();
        $this->outputMain(true);
    }

    public function editor(): void
    {
        $this->verifyPermissions($this->domain, 'perm_users_manage');
        $output_panel = new OutputPanelUsers($this->domain, false);
        $output_panel->edit(['username' => $this->username], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $this->verifyPermissions($this->domain, 'perm_users_manage');
        $update_user = $this->authorization->getUser($this->username);

        if ($update_user->empty()) {
            $update_user = $this->authorization->newUser($this->username);
        }

        foreach ($_POST as $key => $value) // TODO: Improve this
        {
            if (is_array($value)) {
                $value = nel_form_input_default($value);
            }

            if (strpos($key, 'domain_role') !== false) {
                if (strpos($key, Domain::SITE)) {
                    $domain = new DomainSite($this->database);
                } else if (strpos($key, Domain::GLOBAL)) {
                    $domain = new DomainGlobal($this->database);
                } else {
                    $domain = new DomainBoard(utf8_substr($key, 12), $this->database);
                }

                $update_user->modifyRole($domain->id(), $value);
                continue;
            }

            if ($key === 'password') {
                if (!empty($value)) {
                    $update_user->changeData('password', nel_password_hash($value, NEL_PASSWORD_ALGORITHM));
                }

                continue;
            }

            $update_user->changeData($key, $value);
        }

        $this->authorization->saveUsers();
        $update_user->loadFromDatabase();
        $this->outputMain(true);
    }

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_users_manage');
        $this->authorization->removeUser($this->username);
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_users_view':
                nel_derp(395, _gettext('You are not allowed to view users.'));
                break;

            case 'perm_users_manage':
                nel_derp(396, _gettext('You are not allowed to manage users.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
