<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelTemplates;

class AdminTemplates extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_TEMPLATES_TABLE;
        $this->id_column = 'template_id';
        $this->panel_name = _gettext('Templates');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_templates');
        $output_panel = new OutputPanelTemplates($this->domain, false);
        $output_panel->render([], false);
    }

    public function install(string $template_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_templates');
        $this->domain->frontEndData()->getTemplate($template_id)->install();
        $this->domain->templatePath($this->domain->frontEndData()->getTemplate($template_id)->getPath());
        $this->panel();
    }

    public function uninstall(string $template_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_templates');
        $this->domain->frontEndData()->getTemplate($template_id)->uninstall();
        $this->domain->templatePath($this->domain->frontEndData()->getTemplate($template_id)->getPath());
        $this->panel();
    }

    public function enable(string $template_id)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_templates');
        $this->domain->frontEndData()->getTemplate($template_id)->enable();
        $this->panel();
    }

    public function disable(string $template_id)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_templates');
        $this->domain->frontEndData()->getTemplate($template_id)->disable();
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_manage_templates':
                nel_derp(390, _gettext('You are not allowed to manage templates.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}
