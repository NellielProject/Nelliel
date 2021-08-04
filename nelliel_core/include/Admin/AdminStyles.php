<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Assets\Style;

class AdminStyles extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_STYLES_TABLE;
        $this->id_field = 'style-id';
        $this->id_column = 'style_id';
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Output\OutputPanelStyles($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {
        $this->verifyAccess($this->domain);
    }

    public function add(): void
    {
        $id = $_GET[$this->id_field] ?? '';
        $this->verifyAction(nel_site_domain());
        $this->domain->frontEndData()->getStyle($id)->install();
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
        $this->domain->frontEndData()->getStyle($id)->uninstall();
        $this->outputMain(true);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_styles'))
        {
            nel_derp(410, _gettext('You do not have access to the Styles panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_styles'))
        {
            nel_derp(411, _gettext('You are not allowed to manage styles.'));
        }
    }
}
