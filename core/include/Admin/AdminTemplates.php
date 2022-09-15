<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class AdminTemplates extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_TEMPLATES_TABLE;
        $this->id_field = 'template-id';
        $this->id_column = 'template_id';
        $this->panel_name = _gettext('Templates');
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
        $this->verifyPermissions($this->domain, 'perm_templates_manage');
        $output_panel = new \Nelliel\Output\OutputPanelTemplates($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {}

    public function install(string $template_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_templates_manage');
        $this->domain->frontEndData()->getTemplate($template_id)->install();
        $this->domain->templatePath($this->domain->frontEndData()->getTemplate($template_id)->getPath());
        $this->panel();
    }

    public function editor(): void
    {}

    public function update(): void
    {}

    public function uninstall(string $template_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_templates_manage');
        $this->domain->frontEndData()->getTemplate($template_id)->uninstall();
        $this->domain->templatePath($this->domain->frontEndData()->getTemplate($template_id)->getPath());
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_templates_manage':
                nel_derp(390, _gettext('You are not allowed to manage templates.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    public function enable(string $template_id)
    {
        $this->verifyPermissions($this->domain, 'perm_templates_manage');
        $this->domain->frontEndData()->getTemplate($template_id)->enable();
        $this->panel();
    }

    public function disable(string $template_id)
    {
        $this->verifyPermissions($this->domain, 'perm_templates_manage');
        $this->domain->frontEndData()->getTemplate($template_id)->disable();
        $this->panel();
    }
}
