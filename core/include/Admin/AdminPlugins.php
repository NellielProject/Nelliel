<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelPlugins;

class AdminPlugins extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_PLUGINS_TABLE;
        $this->id_column = 'plugin_id';
        $this->panel_name = _gettext('Plugins');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_plugins');
        $output_panel = new OutputPanelPlugins($this->domain, false);
        $output_panel->render([], false);
    }

    public function install(string $plugin_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_plugins');
        nel_plugins()->getPlugin($plugin_id)->install();
        $this->panel();
    }

    public function uninstall(string $plugin_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_plugins');
        nel_plugins()->getPlugin($plugin_id)->uninstall();
        $this->panel();
    }

    public function enable(string $plugin_id)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_plugins');
        nel_plugins()->getPlugin($plugin_id)->enable();
        $this->panel();
    }

    public function disable(string $plugin_id)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_plugins');
        nel_plugins()->getPlugin($plugin_id)->disable();
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_manage_plugins':
                nel_derp(385, _gettext('You are not allowed to manage styles.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
