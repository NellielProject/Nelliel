<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class AdminIconSets extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_ICON_SETS_TABLE;
        $this->id_field = 'icon-set-id';
        $this->id_column = 'set_id';
        $this->panel_name = _gettext('Icon Sets');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_icon_sets_manage');
        $output_panel = new \Nelliel\Output\OutputPanelIconSets($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_icon_sets_manage');
        $id = $_GET[$this->id_field] ?? '';
        $this->domain->frontEndData()->getIconSet($id)->install();
        $this->outputMain(true);
    }

    public function editor(): void
    {
    }

    public function update(): void
    {
    }

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_icon_sets_manage');
        $id = $_GET[$this->id_field] ?? '';
        $this->domain->frontEndData()->getIconSet($id)->uninstall();
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
            case 'perm_icon_sets_manage':
                nel_derp(350, _gettext('You are not allowed to manage icon sets.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
