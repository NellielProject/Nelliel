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

    public function actionDispatch($inputs)
    {
        if ($inputs['action'] === 'add')
        {
            $this->add();
        }
        else if ($inputs['action'] == 'remove')
        {
            $this->remove();
        }
        else
        {
            $this->renderPanel();
        }
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelFiletypes($this->domain);
        $output_panel->render(['user' => $this->session_user], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(431, _gettext('You are not allowed to add filetypes.'));
        }

        $extension = $_POST['extension'];
        $parent_extension = $_POST['parent_extension'];
        $type = $_POST['type'];
        $format = $_POST['format'];
        $mime = $_POST['mime'];
        $regex = $_POST['regex'];
        $label = $_POST['label'];
        $prepared = $this->database->prepare(
                'INSERT INTO "' . FILETYPES_TABLE .
                '" ("extension", "parent_extension", "type", "format", "mime", "id_regex", "label") VALUES (?, ?, ?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared,
                [$extension, $parent_extension, $type, $format, $mime, $regex, $label]);

        foreach ($this->getBoardDomains() as $board_domain)
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . $board_domain->reference('config_table') .
                    '" ("config_type", "config_owner", "config_category", "data_type", "config_name", "setting", "select_type", "edit_lock") VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $this->database->executePrepared($prepared,
                    ['filetype_enable', 'nelliel', $type, 'boolean', $format, '0', 0, 0]);
        }

        $this->renderPanel();
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_filetypes'))
        {
            nel_derp(432, _gettext('You are not allowed to remove filetypes.'));
        }

        $filetype_id = $_GET['filetype-id'];
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . FILETYPES_TABLE . '" WHERE "entry" = ?');
        $filetype_info = $this->database->executePreparedFetch($prepared, [$filetype_id], PDO::FETCH_ASSOC);
        $prepared = $this->database->prepare('DELETE FROM "' . FILETYPES_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, [$filetype_id]);

        foreach ($this->getBoardDomains() as $board_domain)
        {
            $prepared = $this->database->prepare(
                    'DELETE FROM "' . $board_domain->reference('config_table') . '" WHERE "config_type" = \'filetype_enable\' AND "config_name" = ?');
            $this->database->executePrepared($prepared, [$filetype_info['format']]);
        }

        $this->renderPanel();
    }

    private function getBoardDomains()
    {
        $query = 'SELECT "board_id" FROM "' . BOARD_DATA_TABLE . '"';
        $board_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);
        $board_domains = array();

        foreach ($board_ids as $board_id)
        {
            $board_domains[] = new \Nelliel\DomainBoard($board_id, $this->database);
        }

        return $board_domains;
    }
}
