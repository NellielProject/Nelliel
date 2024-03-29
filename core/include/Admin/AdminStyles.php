<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelStyles;

class AdminStyles extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_STYLES_TABLE;
        $this->id_column = 'style_id';
        $this->panel_name = _gettext('Styles');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_styles');
        $output_panel = new OutputPanelStyles($this->domain, false);
        $output_panel->render([], false);
    }

    public function install(string $style_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_styles');
        $this->domain->frontEndData()->getStyle($style_id)->install();
        $this->panel();
    }

    public function uninstall(string $style_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_styles');
        $this->domain->frontEndData()->getStyle($style_id)->uninstall();
        $this->panel();
    }

    public function enable(string $style_id)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_styles');
        $this->domain->frontEndData()->getStyle($style_id)->enable();
        $this->panel();
    }

    public function disable(string $style_id)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_styles');
        $this->domain->frontEndData()->getStyle($style_id)->disable();
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_manage_styles':
                nel_derp(385, _gettext('You are not allowed to manage styles.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
