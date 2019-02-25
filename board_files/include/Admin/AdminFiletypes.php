<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;
use Nelliel\Domain;
use Nelliel\Auth\Authorization;

require_once INCLUDE_PATH . 'output/management/filetypes_panel.php';

class AdminFiletypes extends AdminHandler
{

    function __construct($database, Authorization $authorization, Domain $domain)
    {
        $this->database = $database;
        $this->authorization = $authorization;
        $this->domain = $domain;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session($this->authorization, true);
        $user = $session->sessionUser();

        if ($inputs['action'] === 'add')
        {
            $this->add($user);
        }
        else if ($inputs['action'] == 'remove')
        {
            $this->remove($user);
        }
        else
        {
            $this->renderPanel($user);
        }
    }

    public function renderPanel($user)
    {
        nel_render_filetypes_panel($user, $this->domain);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_filetypes_modify'))
        {
            nel_derp(431, _gettext('You are not allowed to modify filetypes.'));
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

        $this->renderPanel($user);
    }

    public function editor($user)
    {
    }

    public function update($user)
    {
    }

    public function remove($user)
    {
        if (!$user->domainPermission($this->domain, 'perm_filetypes_modify'))
        {
            nel_derp(431, _gettext('You are not allowed to modify filetypes.'));
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

        $this->renderPanel($user);
    }

    private function getBoardDomains()
    {
        $query = 'SELECT "board_id" FROM "' . BOARD_DATA_TABLE . '"';
        $board_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);
        $board_domains = array();

        foreach ($board_ids as $board_id)
        {
            $board_domains[] = new \Nelliel\DomainBoard($board_id, new \Nelliel\CacheHandler(), $this->database, new \Nelliel\Language\Translator());
        }

        return $board_domains;
    }
}
