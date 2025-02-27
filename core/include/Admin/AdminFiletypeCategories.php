<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Output\OutputPanelFiletypes;

class AdminFiletypeCategories extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_FILETYPE_CATEGORIES_TABLE;
        $this->panel_name = _gettext('Filetypes');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $output_panel = new OutputPanelFiletypes($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $output_panel = new OutputPanelFiletypes($this->domain, false);
        $output_panel->newCategory(['editing' => false], false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $category = $_POST['category'] ?? '';
        $label = $_POST['label'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table . '" ("category", "label", "enabled") VALUES (?, ?, ?)');
        $this->database->executePrepared($prepared, [$category, $label, $enabled]);
        $this->panel();
    }

    public function editor(string $category): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $output_panel = new OutputPanelFiletypes($this->domain, false);
        $output_panel->editCategory(['editing' => true, 'category' => $category], false);
    }

    public function update(string $category): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $label = $_POST['label'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "label" = ?, "enabled" = ? WHERE "category" = ?');
        $this->database->executePrepared($prepared, [$label, $enabled, $category]);
        $this->panel();
    }

    public function delete(string $category): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "category" = ?');
        $this->database->executePrepared($prepared, [$category]);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_manage_filetypes':
                nel_derp(345, _gettext('You are not allowed to manage filetypes.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    public function enable(string $category)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "category" = ?');
        $this->database->executePrepared($prepared, [$category]);
        $this->panel();
    }

    public function disable(string $category)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "category" = ?');
        $this->database->executePrepared($prepared, [$category]);
        $this->panel();
    }
}
