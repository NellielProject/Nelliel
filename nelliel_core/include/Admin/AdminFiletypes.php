<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;
use Nelliel\Domain;
use Nelliel\Auth\Authorization;

class AdminFiletypes extends AdminHandler
{

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->validateUser();
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelFiletypes($this->domain, false);
        $output_panel->render(['user' => $this->session_user], false);
    }

    public function creator()
    {
        $output_panel = new \Nelliel\Output\OutputPanelFiletypes($this->domain, false);
        $output_panel->edit(['user' => $this->session_user, 'editing' => false], false);
        $this->outputMain(false);
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(431, _gettext('You are not allowed to add filetypes.'));
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
        $entry = $_GET['filetype-id'] ?? 0;
        $output_panel = new \Nelliel\Output\OutputPanelFiletypes($this->domain, false);
        $output_panel->edit(['user' => $this->session_user, 'editing' => true, 'entry' => $entry], false);
        $this->outputMain(false);
    }

    public function update()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(431, _gettext('You are not allowed to add filetypes.'));
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
                '" SET "base_extension" = ?, "type" = ?, "format" = ?, "mime" = ?, "sub_extensions" = ?, "id_regex" = ?, "label" = ?, "enabled" = ? WHERE "entry" = ?');
        $this->database->executePrepared($prepared,
                [$base_extension, $type, $format, $mime, $sub_extensions, $id_regex, $label, $enabled, $filetype_id]);
        $this->outputMain(true);
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(432, _gettext('You are not allowed to remove filetypes.'));
        }

        $filetype_id = $_GET['filetype-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_FILETYPES_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$filetype_id]);
        $this->outputMain(true);
    }

    private function getBoardDomains()
    {
        $query = 'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"';
        $board_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);
        $board_domains = array();

        foreach ($board_ids as $board_id)
        {
            $board_domains[] = new \Nelliel\DomainBoard($board_id, $this->database);
        }

        return $board_domains;
    }

    // TODO: combine these
    public function enable()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(433, _gettext('You are not allowed to enable or disable filetypes.'));
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
            nel_derp(433, _gettext('You are not allowed to enable or disable filetypes.'));
        }

        $filetype_id = $_GET['filetype-id'];
        $prepared = $this->database->prepare('UPDATE "' . NEL_FILETYPES_TABLE . '" SET "enabled" = 0 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$filetype_id]);
        $this->outputMain(true);
    }
}
