<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelPlugins;

class DispatchPlugins extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $go_to_panel = true;
        $plugin_id = strval($inputs['id'] ?? '');

        switch ($inputs['section']) {
            case 'install':
                $this->verifyPermissions($this->domain, 'perm_manage_plugins');
                nel_plugins()->getPlugin($plugin_id)->install();
                nel_plugins()->processHook('nel-in-after-plugin-install', [$plugin_id]);
                break;

            case 'uninstall':
                $this->verifyPermissions($this->domain, 'perm_manage_plugins');
                nel_plugins()->getPlugin($plugin_id)->uninstall();
                nel_plugins()->processHook('nel-in-after-plugin-uninstall', [$plugin_id]);
                break;

            case 'enable':
                $this->verifyPermissions($this->domain, 'perm_manage_plugins');
                nel_plugins()->getPlugin($plugin_id)->enable();
                break;

            case 'disable':
                $this->verifyPermissions($this->domain, 'perm_manage_plugins');
                nel_plugins()->getPlugin($plugin_id)->disable();
                break;

            default:
                ;
        }

        if ($go_to_panel) {
            $this->verifyPermissions($this->domain, 'perm_manage_plugins');
            $output_panel = new OutputPanelPlugins($this->domain, false);
            $output_panel->render([], false);
        }
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session->user()->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_manage_plugins':
                nel_derp(450, _gettext('You are not allowed to manage plugins.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}