<?php

declare(strict_types=1);

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

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->data_table = NEL_FILETYPES_TABLE;
        $this->id_field = 'filetype-id';
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
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Output\OutputPanelFiletypes($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator(): void
    {
        $this->verifyAccess($this->domain);
        $output_panel = new \Nelliel\Output\OutputPanelFiletypes($this->domain, false);
        $output_panel->new(['editing' => false], false);
        $this->outputMain(false);
    }

    public function add(): void
    {
        $this->verifyAction($this->domain);
        $base_extension = $_POST['base_extension'] ?? null;
        $type = $_POST['type'] ?? null;
        $format = $_POST['format'] ?? null;
        $mime = $_POST['mime'] ?? null;
        $id_regex = $_POST['id_regex'] ?? null;
        $label = $_POST['type_label'] ?? null;
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

    public function editor(): void
    {
        $this->verifyAccess($this->domain);
        $entry = $_GET['filetype-id'] ?? 0;
        $output_panel = new \Nelliel\Output\OutputPanelFiletypes($this->domain, false);
        $output_panel->edit(['editing' => true, 'entry' => $entry], false);
        $this->outputMain(false);
    }

    public function update(): void
    {
        $this->verifyAction($this->domain);
        $filetype_id = $_GET['filetype-id'];
        $base_extension = $_POST['base_extension'] ?? null;
        $type = $_POST['type'] ?? null;
        $format = $_POST['format'] ?? null;
        $mime = $_POST['mime'] ?? null;
        $id_regex = $_POST['id_regex'] ?? null;
        $label = $_POST['type_label'] ?? null;
        $type_def = $_POST['type_def'] ?? 0;
        $enabled = $_POST['enabled'] ?? 0;
        $post_sub = $_POST['sub_extensions'] ?? '';
        $sub_explode = explode(' ', $post_sub);
        $sub_extensions = is_array($sub_explode) ? json_encode($sub_explode) : '';

        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_FILETYPES_TABLE .
                '" SET "base_extension" = ?, "type" = ?, "format" = ?, "mime" = ?, "sub_extensions" = ?, "id_regex" = ?, "type_label" = ?, "type_def" = ?, "enabled" = ? WHERE "entry" = ?');
        $this->database->executePrepared($prepared,
                [$base_extension, $type, $format, $mime, $sub_extensions, $id_regex, $label, $type_def, $enabled,
                    $filetype_id]);
        $this->outputMain(true);
    }

    public function remove(): void
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $prepared = $this->database->prepare('DELETE FROM "' . $this->data_table . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function enable()
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 1 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function disable()
    {
        $id = $_GET[$this->id_field] ?? 0;
        $entry_domain = $this->getEntryDomain($id);
        $this->verifyAction($entry_domain);
        $prepared = $this->database->prepare('UPDATE "' . $this->data_table . '" SET "enabled" = 0 WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$id]);
        $this->outputMain(true);
    }

    public function verifyAccess(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(400, _gettext('You do not have access to the Filetypes panel.'));
        }
    }

    public function verifyAction(Domain $domain)
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(401, _gettext('You are not allowed to manage filetypes.'));
        }
    }
}
