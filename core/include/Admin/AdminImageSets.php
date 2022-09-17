<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelImageSets;

class AdminImageSets extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_IMAGE_SETS_TABLE;
        $this->panel_name = _gettext('Image Sets');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_image_sets_manage');
        $output_panel = new OutputPanelImageSets($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {}

    public function install(string $set_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_image_sets_manage');
        $this->domain->frontEndData()->getImageSet($set_id)->install();
        $this->panel();
    }

    public function editor(): void
    {}

    public function update(): void
    {}

    public function uninstall(string $set_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_image_sets_manage');
        $this->domain->frontEndData()->getImageSet($set_id)->uninstall();
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_image_sets_manage':
                nel_derp(350, _gettext('You are not allowed to manage image sets.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    public function enable(string $set_id)
    {
        $this->verifyPermissions($this->domain, 'perm_image_sets_manage');
        $this->domain->frontEndData()->getImageSet($set_id)->enable();
        $this->panel();
    }

    public function disable(string $set_id)
    {
        $this->verifyPermissions($this->domain, 'perm_image_sets_manage');
        $this->domain->frontEndData()->getImageSet($set_id)->disable();
        $this->panel();
    }
}
