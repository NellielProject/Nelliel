<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class AdminImageSets extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_IMAGE_SETS_TABLE;
        $this->id_field = 'image-set-id';
        $this->id_column = 'set_id';
        $this->panel_name = _gettext('Image Sets');
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
        $this->verifyPermissions($this->domain, 'perm_image_sets_manage');
        $output_panel = new \Nelliel\Output\OutputPanelImageSets($this->domain, false);
        $output_panel->render([], false);
    }

    public function creator(): void
    {}

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_image_sets_manage');
        $id = $_GET[$this->id_field] ?? '';
        $this->domain->frontEndData()->getImageSet($id)->install();
        $this->outputMain(true);
    }

    public function editor(): void
    {}

    public function update(): void
    {}

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_image_sets_manage');
        $id = $_GET[$this->id_field] ?? '';
        $this->domain->frontEndData()->getImageSet($id)->uninstall();
        $this->outputMain(true);
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

    public function enable()
    {
        $this->verifyPermissions($this->domain, 'perm_image_sets_manage');
        $id = $_GET[$this->id_field] ?? '';
        $this->domain->frontEndData()->getImageSet($id)->enable();
        $this->outputMain(true);
    }

    public function disable()
    {
        $this->verifyPermissions($this->domain, 'perm_image_sets_manage');
        $id = $_GET[$this->id_field] ?? '';
        $this->domain->frontEndData()->getImageSet($id)->disable();
        $this->outputMain(true);
    }
}
