<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelContentOps;

class AdminContentOps extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_CONTENT_OPS_TABLE;
        $this->panel_name = _gettext('Content Ops');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_content_ops_manage');
        $output_panel = new OutputPanelContentOps($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_content_ops_manage');
        $output_panel = new OutputPanelContentOps($this->domain, false);
        $output_panel->new(['editing' => false], false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_content_ops_manage');
        $label = $_POST['label'] ?? 0;
        $url = $_POST['url'] ?? '';
        $images_only = $_POST['images_only'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;
        $notes = $_POST['notes'] ?? null;
        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table .
            '" ("label", "url", "images_only", "enabled", "notes") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$label, $url, $images_only, $enabled, $notes]);
        $this->panel();
    }

    public function editor(string $op_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_content_ops_manage');
        $output_panel = new OutputPanelContentOps($this->domain, false);
        $output_panel->edit(['editing' => true, 'op_id' => $op_id], false);
    }

    public function update(string $op_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_content_ops_manage');
        $label = $_POST['label'] ?? '';
        $url = $_POST['url'] ?? '';
        $images_only = $_POST['images_only'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;
        $notes = $_POST['notes'] ?? null;

        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table .
            '" SET "label" = ?, "url" = ?, "images_only" = ?, "enabled" = ?, "notes" = ? WHERE "op_id" = ?');
        $this->database->executePrepared($prepared, [$label, $url, $images_only, $enabled, $notes, $op_id]);
        $this->panel();
    }

    public function delete(string $op_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_content_ops_manage');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "op_id" = ?');
        $this->database->executePrepared($prepared, [$op_id]);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_content_ops_manage':
                nel_derp(420, _gettext('You are not allowed to manage content ops.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    public function enable(string $op_id)
    {
        $this->verifyPermissions($this->domain, 'perm_content_ops_manage');
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "op_id" = ?');
        $this->database->executePrepared($prepared, [$op_id]);
        $this->panel();
    }

    public function disable(string $op_id)
    {
        $this->verifyPermissions($this->domain, 'perm_content_ops_manage');
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "op_id" = ?');
        $this->database->executePrepared($prepared, [$op_id]);
        $this->panel();
    }
}
