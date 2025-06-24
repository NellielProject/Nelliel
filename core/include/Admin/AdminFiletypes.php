<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Output\OutputPanelFiletypes;

class AdminFiletypes extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_FILETYPES_TABLE;
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
        $output_panel->newFiletype(['editing' => false], false);
    }

    public function add(): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $format = $_POST['format'] ?? '';
        $extensions = $_POST['extensions'] ?? '';
        $category = $_POST['category'] ?? null;
        $mimetypes = $_POST['mimetypes'] ?? '';
        $magic_regex = $_POST['magic_regex'] ?? '';
        $label = $_POST['label'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;

        $prepared = $this->database->prepare(
            'INSERT INTO "' . $this->data_table .
            '" ("format", "extensions", "category", "mimetypes", "magic_regex", "label", "enabled") VALUES (?, ?, ?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared,
            [$format, $extensions, $category, $mimetypes, $magic_regex, $label, $enabled]);
        $this->panel();
    }

    public function editor(string $format): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $output_panel = new OutputPanelFiletypes($this->domain, false);
        $output_panel->editFiletype(['editing' => true, 'format' => $format], false);
    }

    public function update(string $format): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $extensions = $_POST['extensions'] ?? '';
        $category = $_POST['category'] ?? null;
        $mimetypes = $_POST['mimetypes'] ?? '';
        $magic_regex = $_POST['magic_regex'] ?? '';
        $label = $_POST['label'] ?? '';
        $enabled = $_POST['enabled'] ?? 0;

        $prepared = $this->database->prepare(
            'UPDATE "' . $this->data_table .
            '" SET "extensions" = ?, "category" = ?, "mimetypes" = ?, "magic_regex" = ?, "label" = ?, "enabled" = ? WHERE "format" = ?');
        $this->database->executePrepared($prepared,
            [$extensions, $category, $mimetypes, $magic_regex, $label, $enabled, $format]);
        $this->panel();
    }

    public function delete(string $format): void
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "format" = ?');
        $this->database->executePrepared($prepared, [$format]);
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

    public function enable(string $format)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "format" = ?');
        $this->database->executePrepared($prepared, [$format]);
        $this->panel();
    }

    public function disable(string $format)
    {
        $this->verifyPermissions($this->domain, 'perm_manage_filetypes');
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "format" = ?');
        $this->database->executePrepared($prepared, [$format]);
        $this->panel();
    }
}
