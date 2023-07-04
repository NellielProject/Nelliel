<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelMarkup;

class AdminMarkup extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_MARKUP_TABLE;
        $this->panel_name = _gettext('Markup');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_markup_manage');
        $output_panel = new OutputPanelMarkup($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_markup_manage');
        $output_panel = new OutputPanelMarkup($this->domain, false);
        $output_panel->new(['editing' => false], false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_markup_manage');
        $label = $_POST['label'] ?? '';
        $type = $_POST['type'] ?? '';
        $match = $_POST['match'] ?? '';
        $replace = $_POST['replace'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;
        $notes = $_POST['notes'] ?? '';
        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table .
            '" ("label", "type", "match", "replace", "enabled", "notes") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$label, $type, $match, $replace, $enabled, $notes]);
        $this->panel();
    }

    public function editor(string $markup_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_markup_manage');
        $output_panel = new OutputPanelMarkup($this->domain, false);
        $output_panel->edit(['editing' => true, 'markup_id' => $markup_id], false);
    }

    public function update(string $markup_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_markup_manage');
        $label = $_POST['label'] ?? '';
        $type = $_POST['type'] ?? '';
        $match = $_POST['match'] ?? '';
        $replace = $_POST['replace'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;
        $notes = $_POST['notes'] ?? '';

        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table .
            '" SET "label" = ?, "type" = ?, "match" = ?, "replace" = ?, "enabled" = ?, "notes" = ? WHERE "markup_id" = ?');
        $this->database->executePrepared($prepared, [$label, $type, $match, $replace, $enabled, $notes, $markup_id]);
        $this->panel();
    }

    public function delete(string $markup_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_markup_manage');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "markup_id" = ?');
        $this->database->executePrepared($prepared, [$markup_id]);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_markup_manage':
                nel_derp(0, _gettext('You are not allowed to manage markup.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    public function enable(string $markup_id)
    {
        $this->verifyPermissions($this->domain, 'perm_markup_manage');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "markup_id" = ?');
        $this->database->executePrepared($prepared, [$markup_id]);
        $this->panel();
    }

    public function disable(string $markup_id)
    {
        $this->verifyPermissions($this->domain, 'perm_markup_manage');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "markup_id" = ?');
        $this->database->executePrepared($prepared, [$markup_id]);
        $this->panel();
    }
}
