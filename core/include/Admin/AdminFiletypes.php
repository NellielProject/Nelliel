<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Output\OutputPanelFiletypes;

class AdminFiletypes extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_FILETYPES_TABLE;
        $this->id_field = 'filetype-id';
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
        $output_panel->newFiletype(['editing' => false], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $format = $_POST['format'] ?? '';
        $extensions = $_POST['extensions'] ?? '';
        $category = $_POST['category'] ?? null;
        $mime = $_POST['mime'] ?? '';
        $magic_regex = $_POST['magic_regex'] ?? '';
        $label = $_POST['label'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;

        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table .
            '" ("format", "extensions", "category", "mime", "magic_regex", "label", "enabled") VALUES (?, ?, ?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared,
            [$format, $extensions, $category, $mime, $magic_regex, $label, $enabled]);
        $this->outputMain(true);
    }

    public function editor(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $format = $_GET['filetype-id'] ?? '';
        $output_panel = new OutputPanelFiletypes($this->domain, false);
        $output_panel->editFiletype(['editing' => true, 'format' => $format], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $format = $_GET['filetype-id'] ?? '';
        $extensions = $_POST['extensions'] ?? '';
        $category = $_POST['category'] ?? null;
        $mime = $_POST['mime'] ?? '';
        $magic_regex = $_POST['magic_regex'] ?? '';
        $label = $_POST['label'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;

        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table .
            '" SET "extensions" = ?, "category" = ?, "mime" = ?, "magic_regex" = ?, "label" = ?, "enabled" = ? WHERE "format" = ?');
        $this->database->executePrepared($prepared,
            [$extensions, $category, $mime, $magic_regex, $label, $enabled, $format]);
        $this->outputMain(true);
    }

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $format = $_GET[$this->id_field] ?? '';
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "format" = ?');
        $this->database->executePrepared($prepared, [$format]);
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
        $format = $_GET[$this->id_field] ?? '';
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "format" = ?');
        $this->database->executePrepared($prepared, [$format]);
        $this->outputMain(true);
    }

    public function disable()
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $format = $_GET[$this->id_field] ?? '';
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "format" = ?');
        $this->database->executePrepared($prepared, [$format]);
        $this->outputMain(true);
    }
}
