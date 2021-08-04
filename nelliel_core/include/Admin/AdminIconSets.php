<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Assets\IconSet;

class AdminIconSets extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_ICON_SETS_TABLE;
        $this->id_field = 'icon-set-id';
        $this->id_column = 'set_id';
    }

    public function dispatch(array $inputs): void
    {
        parent::dispatch($inputs);
    }

    public function panel(): void
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Output\OutputPanelIconSets($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {
    }

    public function add(): void
    {
        $id = $_GET[$this->id_field] ?? 0;
        $this->verifyAction(nel_site_domain());
        $this->domain->frontEndData()->getIconSet($id)->install();
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
        $id = $_GET[$this->id_field] ?? 0;
        $this->verifyAction(nel_site_domain());
        $this->domain->frontEndData()->getIconSet($id)->uninstall();
        $this->outputMain(true);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_icon_sets'))
        {
            nel_derp(430, _gettext('You do not have access to the Icon Sets panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_icon_sets'))
        {
            nel_derp(431, _gettext('You are not allowed to manage icon sets.'));
        }
    }
}
