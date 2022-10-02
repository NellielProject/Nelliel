<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelEmbeds;

class AdminEmbeds extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_EMBEDS_TABLE;
        $this->panel_name = _gettext('Embeds');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_embeds');
        $output_panel = new OutputPanelEmbeds($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_embeds');
        $output_panel = new OutputPanelEmbeds($this->domain, false);
        $output_panel->new(['editing' => false], false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_embeds');
        $label = $_POST['label'] ?? '';
        $regex = $_POST['regex'] ?? '';
        $url = $_POST['url'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;
        $notes = $_POST['notes'] ?? null;
        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table .
            '" ("label", "regex", "url", "enabled", "notes") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$label, $regex, $url, $enabled, $notes]);
        $this->panel();
    }

    public function editor(string $embed_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_embeds');
        $output_panel = new OutputPanelEmbeds($this->domain, false);
        $output_panel->edit(['editing' => true, 'embed_id' => $embed_id], false);
    }

    public function update(string $embed_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_embeds');
        $label = $_POST['label'] ?? '';
        $regex = $_POST['regex'] ?? '';
        $url = $_POST['url'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;
        $notes = $_POST['notes'] ?? null;

        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table .
            '" SET "label" = ?, "regex" = ?, "url" = ?, "enabled" = ?, "notes" = ? WHERE "embed_id" = ?');
        $this->database->executePrepared($prepared, [$label, $regex, $url, $enabled, $notes, $embed_id]);
        $this->panel();
    }

    public function delete(string $embed_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_embeds');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "embed_id" = ?');
        $this->database->executePrepared($prepared, [$embed_id]);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_manage_embeds':
                nel_derp(405, _gettext('You are not allowed to manage embeds.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    public function enable(string $embed_id)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_embeds');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "embed_id" = ?');
        $this->database->executePrepared($prepared, [$embed_id]);
        $this->panel();
    }

    public function disable(string $embed_id)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_embeds');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "embed_id" = ?');
        $this->database->executePrepared($prepared, [$embed_id]);
        $this->panel();
    }
}
