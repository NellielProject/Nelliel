<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Regen;
use Nelliel\SQLCompatibility;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Nelliel\Domains\DomainSite;
use Nelliel\Render\OutputPanelManageBoards;
use Nelliel\Setup\Setup;
use Nelliel\Utility\FileHandler;
use PDO;

class AdminBoards extends Admin
{
    private $site_domain;

    function __construct(Authorization $authorization, Domain $domain, Session $session, array $inputs)
    {
        parent::__construct($authorization, $domain, $session, $inputs);
        $this->site_domain = new DomainSite($this->database);
    }

    public function renderPanel()
    {
        $this->verifyAccess();
        $output_panel = new OutputPanelManageBoards($this->domain, false);
        $output_panel->main([], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_boards'))
        {
            nel_derp(371, _gettext('You are not allowed to create boards.'));
        }

        $site_domain = new DomainSite($this->database);
        $board_id = trim($_POST['new_board_id']);

        if (nel_true_empty($board_id))
        {
            nel_derp(243, _gettext('No board ID provided.'));
        }

        if ($site_domain->setting('only_alphanumeric_board_ids'))
        {
            if (preg_match('/[^a-zA-Z0-9]/', $board_id) === 1)
            {
                nel_derp(242, _gettext('Board ID contains invalid characters. Must be alphanumeric only.'));
            }
        }

        if ($board_id === Domain::SITE || $board_id === Domain::ALL_BOARDS || $board_id === Domain::MULTI_BOARD)
        {
            nel_derp(244, _gettext('Board ID is reserved.'));
        }

        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$board_id], PDO::FETCH_COLUMN);

        if ($result)
        {
            nel_derp(240, _gettext('There is already a board with the ID ' . $board_id . '.'));
        }

        $board_uri = trim($_POST['new_board_uri']);
        $board_uri = (!empty($board_uri)) ? $board_uri : $board_id;
        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_uri" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$board_uri], PDO::FETCH_COLUMN);

        if ($result)
        {
            nel_derp(245, _gettext('There is already a board with the URI ' . $board_uri . '.'));
        }

        if ($board_uri === NEL_TEMPLATES_DIR || $board_uri === NEL_ASSETS_DIR || $board_uri === 'nelliel_core' ||
                $board_uri === 'documentation' || $board_uri === 'tests')
        {
            nel_derp(246, _gettext('Board URI is reserved.'));
        }

        $db_prefix = $this->generateDBPrefix($board_id);

        if ($db_prefix === '')
        {
            nel_derp(241, _gettext('Had trouble registering the board ID ' . $board_id . '. May want to change it.'));
        }

        $hashed_board_id = hash('sha256', $board_id);
        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_BOARD_DATA_TABLE .
                '" ("board_id", "board_uri", "hashed_board_id", "db_prefix") VALUES (?, ?, ?, ?)');
        $prepared->bindValue(1, $board_id, PDO::PARAM_STR);
        $prepared->bindValue(2, $board_uri, PDO::PARAM_STR);
        $prepared->bindValue(3, nel_prepare_hash_for_storage($hashed_board_id), PDO::PARAM_LOB);
        $prepared->bindValue(4, $db_prefix, PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
        $setup = new Setup($this->database, new SQLCompatibility($this->database), new FileHandler());
        $setup->createBoardTables($board_id, $db_prefix);
        $setup->createBoardDirectories($board_id);
        $domain = new DomainBoard($board_id, $this->database);
        $regen = new Regen();
        $domain->regenCache();
        $regen->allBoardPages($domain);
        $regen->boardList(new DomainSite($this->database));
        $this->outputMain(true);
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_boards'))
        {
            nel_derp(373, _gettext('You are not allowed to delete boards.'));
        }

        $board_id = $_GET['board_id'];
        $domain = new DomainBoard($board_id, $this->database);

        if (!$domain->boardExists())
        {
            nel_derp(160, _gettext('Board does not appear to exist.'));
        }

        if ($this->database->tableExists($domain->reference('config_table')) . '"')
        {
            $this->database->query('DROP TABLE "' . $domain->reference('config_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('config_table')]);
        }

        if ($this->database->tableExists($domain->reference('content_table')))
        {
            $this->database->query('DROP TABLE "' . $domain->reference('content_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('content_table')]);
        }

        if ($this->database->tableExists($domain->reference('posts_table')))
        {
            $this->database->query('DROP TABLE "' . $domain->reference('posts_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('posts_table')]);
        }

        if ($this->database->tableExists($domain->reference('threads_table')))
        {
            $this->database->query('DROP TABLE "' . $domain->reference('threads_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('threads_table')]);
        }

        if ($this->database->tableExists($domain->reference('log_table')))
        {
            $this->database->query('DROP TABLE "' . $domain->reference('log_table') . '"');
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('log_table')]);
        }

        nel_utilities()->fileHandler()->eraserGun($domain->reference('board_path'));
        $domain->deleteCache();
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
        $regen = new Regen();
        $regen->boardList($this->domain);
        $this->outputMain(true);
    }

    public function unlock()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_boards'))
        {
            nel_derp(374, _gettext('You are not allowed to unlock this board.'));
        }

        $board_id = $_GET['board_id'];
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_BOARD_DATA_TABLE . '" SET "locked" = 0 WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
        $this->outputMain(true);
    }

    public function lock()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_boards'))
        {
            nel_derp(375, _gettext('You are not allowed to lock this board.'));
        }

        $board_id = $_GET['board_id'];
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_BOARD_DATA_TABLE . '" SET "locked" = 1 WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
        $this->outputMain(true);
    }

    public function createInterstitial(string $which)
    {
        $this->verifyAccess();
        $output_panel = new OutputPanelManageBoards($this->domain, false);

        switch ($which)
        {
            case 'remove_warning':
                $output_panel->removeWarning([], false);
                break;
        }

        $this->outputMain(false);
    }

    // While most engines can handle unicode, there is potential for issues
    // We also have to account for table name max lengths (especially postgresql's tiny 63 byte limit)
    protected function generateDBPrefix(string $board_id)
    {
        $ascii_id = preg_replace('/[^a-zA-Z0-9_]/', '', $board_id);
        $final_id = '';

        for ($i = 0; $i <= 10; $i ++)
        {
            if (strlen($ascii_id) <= 0)
            {
                $test_id = '_board_' . nel_random_alphanumeric(8);
            }
            else
            {
                $truncated_id = substr($ascii_id, 0, 12);
                $test_id = '_board_' . strtolower($truncated_id);
            }

            $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "db_prefix" = ?');
            $result = $this->database->executePreparedFetch($prepared, [$test_id], PDO::FETCH_COLUMN);

            if (!$result)
            {
                $final_id = $test_id;
                break;
            }
        }

        return $final_id;
    }

    private function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_boards'))
        {
            nel_derp(370, _gettext('You are not allowed to access the manage boards panel.'));
        }
    }
}
