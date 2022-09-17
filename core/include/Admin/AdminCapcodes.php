<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelCapcodes;

class AdminCapcodes extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_CAPCODES_TABLE;
        $this->panel_name = _gettext('Capcodes');
    }

    public function panel(): void
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $output_panel = new OutputPanelCapcodes($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $output_panel = new OutputPanelCapcodes($this->domain, false);
        $output_panel->new(['editing' => false], false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $capcode = $_POST['capcode'] ?? '';
        $output = $_POST['output'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table . '" ("capcode", "output", "enabled") VALUES (?, ?, ?)');
        $this->database->executePrepared($prepared, [$capcode, $output, $enabled]);
        $this->panel();
    }

    public function editor(string $capcode_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $output_panel = new OutputPanelCapcodes($this->domain, false);
        $output_panel->edit(['editing' => true, 'capcode_id' => $capcode_id], false);
    }

    public function update(string $capcode_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $capcode = $_POST['capcode'] ?? '';
        $output = $_POST['output'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;

        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "capcode" = ?, "output" = ?, "enabled" = ? WHERE "capcode_id" = ?');
        $this->database->executePrepared($prepared, [$capcode, $output, $enabled, $capcode_id]);
        $this->panel();
    }

    public function delete(string $capcode_id): void
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "capcode_id" = ?');
        $this->database->executePrepared($prepared, [$capcode_id]);
        $this->panel();
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_capcodes_manage':
                nel_derp(425, _gettext('You are not allowed to manage capcodes.'));
                break;

            default:
                $this->defaultPermissionError();
        }
    }

    public function enable(string $capcode_id)
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "capcode_id" = ?');
        $this->database->executePrepared($prepared, [$capcode_id]);
        $this->panel();
    }

    public function disable(string $capcode_id)
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "capcode_id" = ?');
        $this->database->executePrepared($prepared, [$capcode_id]);
        $this->panel();
    }
}
