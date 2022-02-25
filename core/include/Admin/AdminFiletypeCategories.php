<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Output\OutputPanelFiletypes;

class AdminFiletypeCategories extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_FILETYPE_CATEGORIES_TABLE;
        $this->id_field = 'category-id';
        $this->panel_name = _gettext('Filetypes');
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
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $output_panel = new OutputPanelFiletypes($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $output_panel = new OutputPanelFiletypes($this->domain, false);
        $output_panel->newCategory(['editing' => false], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $category = $_POST['category'] ?? '';
        $label = $_POST['label'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;

        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table . '" ("category", "label", "enabled") VALUES (?, ?, ?)');
        $this->database->executePrepared($prepared, [$category, $label, $enabled]);
        $this->outputMain(true);
    }

    public function editor(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $category = $_GET['category-id'] ?? '';
        $output_panel = new OutputPanelFiletypes($this->domain, false);
        $output_panel->editCategory(['editing' => true, 'category' => $category], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $category = $_GET['category-id'] ?? '';
        $label = $_POST['label'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;

        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET ""label" = ?, "enabled" = ? WHERE "category" = ?');
        $this->database->executePrepared($prepared, [$label, $enabled, $category]);
        $this->outputMain(true);
    }

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $id = $_GET[$this->id_field] ?? '';
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "category" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_filetypes_manage':
                nel_derp(345, _gettext('You are not allowed to manage filetypes.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    public function enable()
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $id = $_GET[$this->id_field] ?? '';
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "category" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function disable()
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $id = $_GET[$this->id_field] ?? '';
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "category" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }
}