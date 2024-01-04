<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelPluginControls;

class DispatchPluginControls extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $plugin_controls_panel = new OutputPanelPluginControls($this->domain, false);
        $plugin_id = strval($inputs['id'] ?? '');
        $this->verifyPermissions($this->domain, 'perm_access_plugin_controls');

        if (empty($plugin_id)) {
            $plugin_controls_panel->main([], false);
        } else {
            $plugin_controls_panel->plugin(['plugin_id' => $plugin_id], false);
        }
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session->user()->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_access_plugin_controls':
                nel_derp(455, _gettext('You are not allowed to access plugin control panels.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}