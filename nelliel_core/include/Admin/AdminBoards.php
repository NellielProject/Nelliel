<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;
use PDO;

class AdminBoards extends AdminHandler
{

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->validateUser();
    }

    public function actionDispatch(string $action, bool $return)
    {
        if ($action === 'add')
        {
            $this->add();
        }
        else if ($action === 'remove')
        {
            if (isset($_GET['action-confirmed']) && $_GET['action-confirmed'] === 'true')
            {
                $this->remove();
            }
            else
            {
                $this->createInterstitial();
            }
        }
        else if ($action === 'lock')
        {
            $this->lock();
        }
        else if ($action === 'unlock')
        {
            $this->unlock();
        }

        if ($return)
        {
            return;
        }

        $this->renderPanel();
    }

    public function renderPanel()
    {
        $output_panel = new \Nelliel\Output\OutputPanelManageBoards($this->domain, false);
        $output_panel->render(['section' => 'panel', 'user' => $this->session_user], false);
    }

    public function creator()
    {
    }

    public function add()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_board_create'))
        {
            nel_derp(371, _gettext('You are not allowed to create boards.'));
        }

        $board_id = $_POST['new_board_id'];
        $board_uri = $_POST['new_board_uri'];
        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$board_id], PDO::FETCH_COLUMN);

        if ($result == 1)
        {
            nel_derp(240, _gettext('There is already a board with the ID ' . $board_id . '.'));
        }

        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_uri" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$board_uri], PDO::FETCH_COLUMN);

        if ($result == 1)
        {
            nel_derp(241, _gettext('There is already a board with that URI ' . $board_uri . '.'));
        }

        $db_prefix = '_' . $board_id;
        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_BOARD_DATA_TABLE . '" ("board_id", "db_prefix", "board_uri") VALUES (?, ?, ?)');
        $this->database->executePrepared($prepared, [$board_id, $db_prefix, $board_uri]);
        $setup = new \Nelliel\Setup\Setup();
        $setup->createBoardTables($board_id, $db_prefix);
        $setup->createBoardDirectories($board_id);
        $domain = new \Nelliel\DomainBoard($board_id, $this->database);
        $regen = new \Nelliel\Regen();

        if (NEL_USE_INTERNAL_CACHE)
        {
            $regen->boardCache($domain);
        }

        $regen->allBoardPages($domain);
        $regen->boardList(new \Nelliel\DomainSite($this->database));
    }

    public function editor()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_board_delete'))
        {
            nel_derp(372, _gettext('You are not allowed to delete boards.'));
        }

        $board_id = $_GET['board_id'];
        $domain = new \Nelliel\DomainBoard($board_id, $this->database);

        if (!$domain->boardExists())
        {
            nel_derp(109, _gettext('Board does not appear to exist.'));
        }

        if ($this->database->tableExists($domain->reference('config_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('config_table'));
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('config_table')]);
        }

        if ($this->database->tableExists($domain->reference('content_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('content_table'));
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('content_table')]);
        }

        if ($this->database->tableExists($domain->reference('posts_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('posts_table'));
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('posts_table')]);
        }

        if ($this->database->tableExists($domain->reference('threads_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('threads_table'));
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('threads_table')]);
        }

        if ($this->database->tableExists($domain->reference('archive_content_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('archive_content_table'));
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('archive_content_table')]);
        }

        if ($this->database->tableExists($domain->reference('archive_posts_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('archive_posts_table'));
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('archive_posts_table')]);
        }

        if ($this->database->tableExists($domain->reference('archive_threads_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('archive_threads_table'));
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_VERSIONS_TABLE . '" WHERE "id" = ?');
            $this->database->executePrepared($prepared, [$domain->reference('archive_threads_table')]);
        }

        $file_handler = new \Nelliel\Utility\FileHandler();
        $file_handler->eraserGun($domain->reference('board_path'));
        $domain->deleteCache();
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
        $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_CITES_TABLE . '" WHERE "source_board" = ? OR "target_board" = ?');
        $this->database->executePrepared($prepared, [$board_id, $board_id]);
        $regen = new \Nelliel\Regen();
        $regen->boardList(new \Nelliel\DomainSite($this->database));
    }

    public function lock()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_board_lock'))
        {
            nel_derp(373, _gettext('You are not allowed to lock this board.'));
        }

        $board_id = $_GET['board_id'];
        $prepared = $this->database->prepare('UPDATE "' . NEL_BOARD_DATA_TABLE . '" SET "locked" = 1 WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
    }

    public function unlock()
    {
        if (!$this->session_user->checkPermission($this->domain, 'perm_board_lock'))
        {
            nel_derp(374, _gettext('You are not allowed to unlock this board.'));
        }

        $board_id = $_GET['board_id'];
        $prepared = $this->database->prepare('UPDATE "' . NEL_BOARD_DATA_TABLE . '" SET "locked" = 0 WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
    }

    public function createInterstitial()
    {
        $message = _gettext(
                'Are you certain you want to delete the board? Everything will be gone and this cannot be undone!');
        $url_constructor = new \Nelliel\URLConstructor();
        $continue_link['href'] = $url_constructor->dynamic(NEL_MAIN_SCRIPT,
                ['module' => 'manage-boards', 'action' => 'remove', 'action-confirmed' => 'true',
                    'board_id' => $_GET['board_id']]);
        $continue_link['text'] = _gettext('Confirm and delete the board.');
        $output_panel = new \Nelliel\Output\OutputPanelManageBoards($this->domain, false);
        $output_panel->render(
                ['section' => 'remove_interstitial', 'user' => $this->session_user, 'message' => $message,
                    'continue_link' => $continue_link], false);
    }
}
