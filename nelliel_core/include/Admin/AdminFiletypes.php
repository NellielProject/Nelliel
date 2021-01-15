<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;

class AdminFiletypes extends Admin
{

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        parent::__construct($authorization, $domain, $session, $inputs);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelFiletypes($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator()
    {
        $this->verifyAccess();
        $output_panel = new \Nelliel\Render\OutputPanelFiletypes($this->domain, false);
        $output_panel->new(['editing' => false], false);
        $this->outputMain(false);
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(521, _gettext('You are not allowed to add filetypes.'));
        }

        $base_extension = $_POST['base_extension'] ?? null;
        $type = $_POST['type'] ?? null;
        $format = $_POST['format'] ?? null;
        $mime = $_POST['mime'] ?? null;
        $id_regex = $_POST['id_regex'] ?? null;
        $label = $_POST['label'] ?? null;
        $type_def = $_POST['type_def'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;
        $post_sub = $_POST['sub_extensions'] ?? '';
        $sub_explode = explode(' ', $post_sub);
        $sub_extensions = is_array($sub_explode) ? json_encode($sub_explode) : '';

        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_FILETYPES_TABLE .
                '" ("base_extension", "type", "format", "mime", "sub_extensions", "id_regex", "label", "type_def", "enabled") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared,
                [$base_extension, $type, $format, $mime, $sub_extensions, $id_regex, $label, $type_def, $enabled]);
        $this->outputMain(true);
    }

    public function editor()
    {
        $this->verifyAccess();
        $entry = $_GET['filetype-id'] ?? 0;
        $output_panel = new \Nelliel\Render\OutputPanelFiletypes($this->domain, false);
        $output_panel->edit(['editing' => true, 'entry' => $entry], false);
        $this->outputMain(false);
    }

    public function update()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(522, _gettext('You are not allowed to modify filetypes.'));
        }

        $filetype_id = $_GET['filetype-id'];
        $base_extension = $_POST['base_extension'] ?? null;
        $type = $_POST['type'] ?? null;
        $format = $_POST['format'] ?? null;
        $mime = $_POST['mime'] ?? null;
        $id_regex = $_POST['id_regex'] ?? null;
        $label = $_POST['label'] ?? null;
        $type_def = $_POST['type_def'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;
        $post_sub = $_POST['sub_extensions'] ?? '';
        $sub_explode = explode(' ', $post_sub);
        $sub_extensions = is_array($sub_explode) ? json_encode($sub_explode) : '';

        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_FILETYPES_TABLE .
                '" SET "base_extension" = ?, "type" = ?, "format" = ?, "mime" = ?, "sub_extensions" = ?, "id_regex" = ?, "label" = ?, "type_def" = ?, "enabled" = ? WHERE "entry" = ?');
        $this->database->executePrepared($prepared,
                [$base_extension, $type, $format, $mime, $sub_extensions, $id_regex, $label, $type_def, $enabled,
                    $filetype_id]);
        $this->outputMain(true);
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(523, _gettext('You are not allowed to remove filetypes.'));
        }

        $filetype_id = $_GET['filetype-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_FILETYPES_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$filetype_id]);
        $this->outputMain(true);
    }

    public function enable()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(524, _gettext('You are not allowed to enable filetypes.'));
        }

        $filetype_id = $_GET['filetype-id'];
        $prepared = $this->database->prepare('UPDATE "' . NEL_FILETYPES_TABLE . '" SET "enabled" = 1 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$filetype_id]);
        $this->outputMain(true);
    }

    public function disable()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(525, _gettext('You are not allowed to disable filetypes.'));
        }

        $filetype_id = $_GET['filetype-id'];
        $prepared = $this->database->prepare('UPDATE "' . NEL_FILETYPES_TABLE . '" SET "enabled" = 0 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$filetype_id]);
        $this->outputMain(true);
    }

    private function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(520, _gettext('You are not allowed to access the filetypes panel.'));
        }
    }
}
