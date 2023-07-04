<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelScripts;

class AdminScripts extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_SCRIPTS_TABLE;
        $this->panel_name = _gettext('Scripts');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_scripts');
        $output_panel = new OutputPanelScripts($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_scripts');
        $output_panel = new OutputPanelScripts($this->domain, false);
        $output_panel->new(['editing' => false], false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_scripts');
        $label = $_POST['label'] ?? '';
        $location = $_POST['location'] ?? '';
        $full_url = $_POST['full_url'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;
        $notes = $_POST['notes'] ?? null;
        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table .
            '" ("label", "location", "full_url", "enabled", "notes") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$label, $location, $full_url, $enabled, $notes]);
        $this->panel();
    }

    public function editor(string $script_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_scripts');
        $output_panel = new OutputPanelScripts($this->domain, false);
        $output_panel->edit(['editing' => true, 'script_id' => $script_id], false);
    }

    public function update(string $script_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_scripts');
        $label = $_POST['label'] ?? '';
        $location = $_POST['location'] ?? '';
        $full_url = $_POST['full_url'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;
        $notes = $_POST['notes'] ?? null;

        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table .
            '" SET "label" = ?, "location" = ?, "full_url" = ?, "enabled" = ?, "notes" = ? WHERE "script_id" = ?');
        $this->database->executePrepared($prepared, [$label, $location, $full_url, $enabled, $notes, $script_id]);
        $this->panel();
    }

    public function delete(string $script_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_scripts');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "script_id" = ?');
        $this->database->executePrepared($prepared, [$script_id]);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_manage_scripts':
                nel_derp(435, _gettext('You are not allowed to manage scripts.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    public function enable(string $script_id)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_scripts');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "script_id" = ?');
        $this->database->executePrepared($prepared, [$script_id]);
        $this->panel();
    }

    public function disable(string $script_id)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_scripts');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "script_id" = ?');
        $this->database->executePrepared($prepared, [$script_id]);
        $this->panel();
    }
}
