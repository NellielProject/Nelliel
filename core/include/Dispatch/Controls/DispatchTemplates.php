<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelTemplates;

class DispatchTemplates extends Dispatch
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
        $template_id = strval($inputs['id'] ?? '');

        switch ($inputs['section']) {
            case 'install':
                $this->verifyPermissions($this->domain, 'perm_manage_templates');
                $this->domain->frontEndData()->getTemplate($template_id)->install();
                $this->domain->templatePath($this->domain->frontEndData()->getTemplate($template_id)->getPath());
                break;

            case 'uninstall':
                $this->verifyPermissions($this->domain, 'perm_manage_templates');
                $this->domain->frontEndData()->getTemplate($template_id)->uninstall();
                $this->domain->templatePath($this->domain->frontEndData()->getTemplate($template_id)->getPath());
                break;

            case 'enable':
                $this->verifyPermissions($this->domain, 'perm_manage_templates');
                $this->domain->frontEndData()->getTemplate($template_id)->enable();
                $this->domain->templatePath($this->domain->frontEndData()->getTemplate($template_id)->getPath());
                break;

            case 'disable':
                $this->verifyPermissions($this->domain, 'perm_manage_templates');
                $this->domain->frontEndData()->getTemplate($template_id)->disable();
                $this->domain->templatePath($this->domain->frontEndData()->getTemplate($template_id)->getPath());
                break;

            default:
                ;
        }

        if ($go_to_panel) {
            $this->verifyPermissions($this->domain, 'perm_manage_templates');
            $output_panel = new OutputPanelTemplates($this->domain, false);
            $output_panel->render([], false);
        }
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session->user()->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_manage_templates':
                nel_derp(390, _gettext('You are not allowed to manage templates.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}