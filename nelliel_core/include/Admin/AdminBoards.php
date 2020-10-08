<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\DomainSite;
use Nelliel\Auth\Authorization;
use PDO;

class AdminBoards extends AdminHandler
{
    private $site_domain;

    function __construct(Authorization $authorization, Domain $domain)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->site_domain = new DomainSite($this->database);
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

        $site_domain = new \Nelliel\DomainSite($this->database);
        $board_id = $_POST['new_board_id'];

        if($site_domain->setting('only_alphanumeric_board_ids'))
        {
            if(preg_match('/[^a-zA-Z0-9]/', $board_id) === 1)
            {
                nel_derp(242, _gettext('Board ID contains invalid characters!'));
            }
        }

        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_id" = ? OR "board_uri" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$board_id, $board_id], PDO::FETCH_COLUMN);

        if ($result)
        {
            nel_derp(240, _gettext('There is already a board with the ID ' . $board_id . '.'));
        }

        $db_prefix = $this->generateDBPrefix($board_id);

        if($db_prefix === '')
        {
            nel_derp(241, _gettext('Had trouble registering the board ID ' . $board_id . '. May want to change it.'));
        }

        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_BOARD_DATA_TABLE . '" ("board_id", "board_uri", "db_prefix") VALUES (?, ?, ?)');
        $this->database->executePrepared($prepared, [$board_id, $board_id, $db_prefix]);
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

        $file_handler = new \Nelliel\Utility\FileHandler();
        $file_handler->eraserGun($domain->reference('board_path'));
        $domain->deleteCache();
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
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
                    'board_id' => $_GET['board_id'], 'domain_id' => '_site_']);
        $continue_link['text'] = _gettext('Confirm and delete the board.');
        $output_panel = new \Nelliel\Output\OutputPanelManageBoards($this->domain, false);
        $output_panel->render(
                ['section' => 'remove_interstitial', 'user' => $this->session_user, 'message' => $message,
                    'continue_link' => $continue_link], false);
    }

    // While most engines can handle unicode, there is potential for issues
    // We also have to account for table name max lengths (especially postgresql's tiny 63 byte limit)
    protected function generateDBPrefix(string $board_id)
    {
        $ascii_id = preg_replace('/[^a-zA-Z0-9_]/', '', $board_id);

        $valid = false;
        $final_id = '';

        for($i = 0; $i <= 10; $i ++)
        {
            if(strlen($ascii_id) <= 0)
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

            if(!$result)
            {
                $final_id = $test_id;
                break;
            }
        }

        return $final_id;
    }
}
