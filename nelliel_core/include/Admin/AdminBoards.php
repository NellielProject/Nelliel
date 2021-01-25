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
        $this->verifyAction();
        $site_domain = new DomainSite($this->database);
        $board_uri = trim($_POST['new_board_uri']);

        if (nel_true_empty($board_uri))
        {
            nel_derp(243, _gettext('No board URI provided.'));
        }

        if ($site_domain->setting('only_alphanumeric_board_ids'))
        {
            if (preg_match('/[^a-zA-Z0-9]/', $board_uri) === 1)
            {
                nel_derp(242, _gettext('Board URI contains invalid characters. Must be alphanumeric only.'));
            }
        }

        if ($board_uri === Domain::SITE || $board_uri === Domain::ALL_BOARDS || $board_uri === Domain::MULTI_BOARD || $board_uri === NEL_TEMPLATES_DIR || $board_uri === NEL_ASSETS_DIR || $board_uri === 'nelliel_core' ||
                $board_uri === 'documentation' || $board_uri === 'tests')
        {
            nel_derp(244, _gettext('Board URI is reserved.'));
        }

        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$board_uri], PDO::FETCH_COLUMN);

        if ($result)
        {
            nel_derp(240, _gettext('There is already a board with the URI ' . $board_uri . '.'));
        }

        $db_prefix = $this->generateDBPrefix($board_uri);

        if ($db_prefix === '')
        {
            nel_derp(241, _gettext('Had trouble registering the board URI ' . $board_uri . '. May want to change it.'));
        }

        $hashed_board_id = hash('sha256', $board_uri);
        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_BOARD_DATA_TABLE .
                '" ("board_id", "hashed_board_id", "db_prefix") VALUES (?, ?, ?)');
        $prepared->bindValue(1, $board_uri, PDO::PARAM_STR);
        $prepared->bindValue(2, nel_prepare_hash_for_storage($hashed_board_id), PDO::PARAM_LOB);
        $prepared->bindValue(3, $db_prefix, PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
        $setup = new Setup($this->database, new SQLCompatibility($this->database), new FileHandler());
        $setup->createBoardTables($board_uri, $db_prefix);
        $setup->createBoardDirectories($board_uri);
        $domain = new DomainBoard($board_uri, $this->database);
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
        $this->verifyAction();
        $board_id = $_GET['board-id'];
        $domain = new DomainBoard($board_id, $this->database);

        if (!$domain->exists())
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

    public function enable()
    {
        $this->verifyAction();
    }

    public function disable()
    {
        $this->verifyAction();
    }

    public function makeDefault()
    {
        $this->verifyAction();
    }

    public function unlock()
    {
        $this->verifyAction();
        $board_id = $_GET['board_id'];
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_BOARD_DATA_TABLE . '" SET "locked" = 0 WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
        $this->outputMain(true);
    }

    public function lock()
    {
        $this->verifyAction();
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

    public function verifyAccess()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_boards'))
        {
            nel_derp(370, _gettext('You do not have access to the Manage Boards panel.'));
        }
    }

    public function verifyAction()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_manage_boards'))
        {
            nel_derp(371, _gettext('You are not allowed to manage boards.'));
        }
    }
}
