<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminTemplates extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_TEMPLATES_TABLE;
        $this->id_field = 'template-id';
        $this->id_column = 'template_id';
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Output\OutputPanelTemplates($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {
    }

    public function add(): void
    {
        $id = $_GET[$this->id_field] ?? '';
        $this->verifyAction(nel_site_domain());
        $this->domain->frontEndData()->getTemplate($id)->install();
        $this->domain->templatePath($this->domain->frontEndData()->getTemplate($id)->getPath());
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
        $id = $_GET[$this->id_field] ?? '';
        $this->verifyAction(nel_site_domain());
        $this->domain->frontEndData()->getTemplate($id)->uninstall();
        $this->domain->templatePath($this->domain->frontEndData()->getTemplate($id)->getPath());
        $this->outputMain(true);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_templates'))
        {
            nel_derp(390, _gettext('You do not have access to the Templates panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_templates'))
        {
            nel_derp(391, _gettext('You are not allowed to manage Templates.'));
        }
    }
}
