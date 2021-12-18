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
        $this->id_field = 'capcode-id';
        $this->panel_name = _gettext('Capcodes');
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
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $output_panel = new OutputPanelCapcodes($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $output_panel = new OutputPanelCapcodes($this->domain, false);
        $output_panel->new(['editing' => false], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $capcode = $_POST['capcode'] ?? '';
        $output = $_POST['output'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;
        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table .
            '" ("capcode", "output", "enabled") VALUES (?, ?, ?)');
        $this->database->executePrepared($prepared, [$capcode, $output, $enabled]);
        $this->outputMain(true);
    }

    public function editor(): void
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $entry = $_GET[$this->id_field] ?? 0;
        $output_panel = new OutputPanelCapcodes($this->domain, false);
        $output_panel->edit(['editing' => true, 'entry' => $entry], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $entry = $_GET[$this->id_field] ?? 0;
        $capcode = $_POST['capcode'] ?? '';
        $output = $_POST['output'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;

        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table .
            '" SET "capcode" = ?, "output" = ?, "enabled" = ? WHERE "entry" = ?');
        $this->database->executePrepared($prepared,
            [$capcode, $output, $enabled, $entry]);
        $this->outputMain(true);
    }

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $id = $_GET[$this->id_field] ?? 0;
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
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

    public function enable()
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $id = $_GET[$this->id_field] ?? 0;
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function disable()
    {
        $this->verifyPermissions($this->domain, 'perm_capcodes_manage');
        $id = $_GET[$this->id_field] ?? 0;
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }
}
