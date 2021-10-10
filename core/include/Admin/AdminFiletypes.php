<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

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

        foreach ($inputs['actions'] as $action)
        {
            switch ($action)
            {
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
        $output_panel = new \Nelliel\Output\OutputPanelFiletypes($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $output_panel = new \Nelliel\Output\OutputPanelFiletypes($this->domain, false);
        $output_panel->new(['editing' => false], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $format = $_POST['format'] ?? null;
        $extensions = $_POST['extensions'] ?? null;
        $category = $_POST['category'] ?? null;
        $mime = $_POST['mime'] ?? null;
        $magic_regex = $_POST['magic_regex'] ?? null;
        $label = $_POST['type_label'] ?? '';
        $is_category = $_POST['is_category'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;

        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_FILETYPES_TABLE .
                '" ("format", "extensions", "category", "mime", "magic_regex", "type_label", "is_category", "enabled") VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared,
                [$format, $extensions, $category, $mime, $magic_regex, $label, $is_category, $enabled]);
        $this->outputMain(true);
    }

    public function editor(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $entry = $_GET['filetype-id'] ?? 0;
        $output_panel = new \Nelliel\Output\OutputPanelFiletypes($this->domain, false);
        $output_panel->edit(['editing' => true, 'entry' => $entry], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $filetype_id = $_GET['filetype-id'];
        $format = $_POST['format'] ?? null;
        $extensions = $_POST['extensions'] ?? null;
        $category = $_POST['category'] ?? null;
        $mime = $_POST['mime'] ?? null;
        $magic_regex = $_POST['magic_regex'] ?? null;
        $label = $_POST['type_label'] ?? null;
        $is_category = $_POST['is_category'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;

        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_FILETYPES_TABLE .
                '" SET "format" = ?, "extensions" = ?, "category" = ?, "mime" = ?, "magic_regex" = ?, "type_label" = ?, "is_category" = ?, "enabled" = ? WHERE "entry" = ?');
        $this->database->executePrepared($prepared,
                [$format, $extensions, $category, $mime, $magic_regex, $label, $is_category, $enabled, $filetype_id]);
        $this->outputMain(true);
    }

    public function remove(): void
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $id = $_GET[$this->id_field] ?? 0;
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session_user->checkPermission($domain, $perm))
        {
            return;
        }

        switch ($perm)
        {
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
        $id = $_GET[$this->id_field] ?? 0;
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function disable()
    {
        $this->verifyPermissions($this->domain, 'perm_filetypes_manage');
        $id = $_GET[$this->id_field] ?? 0;
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }
}
