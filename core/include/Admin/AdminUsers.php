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
    private $user_id;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->user_id = $_GET['user-id'] ?? null;
        $this->data_table = NEL_USERS_TABLE;
        $this->id_field = 'user-id';
        $this->id_column = 'user_id';
        $this->panel_name = _gettext('Users');

        if (!is_null($this->user_id) && !$this->authorization->userExists($this->user_id))
        {
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
        $output_panel->new(['user_id' => $this->user_id], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_users_manage');
        $this->user_id = $_POST['user_id'];
        $this->update();
        $this->outputMain(true);
    }

    public function editor(): void
    {
        $this->verifyPermissions($this->domain, 'perm_users_manage');
        $output_panel = new OutputPanelUsers($this->domain, false);
        $output_panel->edit(['user_id' => $this->user_id], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $this->verifyPermissions($this->domain, 'perm_users_manage');
        $update_user = $this->authorization->getUser($this->user_id);

        if ($update_user->empty())
        {
            $update_user = $this->authorization->newUser($this->user_id);
        }

        foreach ($_POST as $key => $value) // TODO: Improve this
        {
            if (is_array($value))
            {
                $value = nel_form_input_default($value);
            }

            if (strpos($key, 'domain_role') !== false)
            {
                if (strpos($key, Domain::SITE))
                {
                    $domain = new DomainSite($this->database);
                }
                else if (strpos($key, Domain::GLOBAL))
                {
                    $domain = new DomainGlobal($this->database);
                }
                else
                {
                    $domain = new DomainBoard(utf8_substr($key, 12), $this->database);
                }

                $update_user->modifyRole($domain->id(), $value);
                continue;
            }

            if ($key === 'user_password')
            {
                if (!empty($value))
                {
                    $update_user->changeData('user_password', nel_password_hash($value, NEL_PASSWORD_ALGORITHM));
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
        $this->authorization->removeUser($this->user_id);
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm))
        {
            return;
        }

        switch ($perm)
        {
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
