<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class AdminStyles extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_STYLES_TABLE;
        $this->id_field = 'style-id';
        $this->id_column = 'style_id';
        $this->panel_name = _gettext('Styles');
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);

        foreach ($inputs['actions'] as $action) {
            switch ($action) {
                case 'disable':
                    $this->disable();
                    break;

                case 'enable':
                    $this->enable();
                    break;
            }
        }
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_styles_manage');
        $output_panel = new \Nelliel\Output\OutputPanelStyles($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {}

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_styles_manage');
        $id = $_GET[$this->id_field] ?? '';
        $this->domain->frontEndData()->getStyle($id)->install();
        $this->outputMain(true);
    }

    public function editor(): void
    {}

    public function update(): void
    {}

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_styles_manage');
        $id = $_GET[$this->id_field] ?? '';
        $this->domain->frontEndData()->getStyle($id)->uninstall();
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_styles_manage':
                nel_derp(385, _gettext('You are not allowed to manage styles.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    public function enable()
    {
        $this->verifyPermissions($this->domain, 'perm_styles_manage');
        $id = $_GET[$this->id_field] ?? '';
        $this->domain->frontEndData()->getStyle($id)->enable();
        $this->outputMain(true);
    }

    public function disable()
    {
        $this->verifyPermissions($this->domain, 'perm_styles_manage');
        $id = $_GET[$this->id_field] ?? '';
        $this->domain->frontEndData()->getStyle($id)->disable();
        $this->outputMain(true);
    }
}
