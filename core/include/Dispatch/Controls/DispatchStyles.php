<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelStyles;

class DispatchStyles extends Dispatch
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
        $style_id = strval($inputs['id'] ?? '');

        switch ($inputs['section']) {
            case 'install':
                $this->verifyPermissions($this->domain, 'perm_manage_styles');
                $this->domain->frontEndData()->getStyle($style_id)->install();
                break;

            case 'uninstall':
                $this->verifyPermissions($this->domain, 'perm_manage_styles');
                $this->domain->frontEndData()->getStyle($style_id)->uninstall();
                break;

            case 'enable':
                $this->verifyPermissions($this->domain, 'perm_manage_styles');
                $this->domain->frontEndData()->getStyle($style_id)->enable();
                break;

            case 'disable':
                $this->verifyPermissions($this->domain, 'perm_manage_styles');
                $this->domain->frontEndData()->getStyle($style_id)->disable();
                break;

            default:
                ;
        }

        if ($go_to_panel) {
            $this->verifyPermissions($this->domain, 'perm_manage_styles');
            $output_panel = new OutputPanelStyles($this->domain, false);
            $output_panel->render([], false);
        }
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session->user()->checkPermission($domain, $perm)) {
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