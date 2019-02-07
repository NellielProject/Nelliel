<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\Auth\Authorization;

require_once INCLUDE_PATH . 'output/management/manage_boards.php';

class AdminBoards extends AdminHandler
{
    private $domain;

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
        else if ($inputs['action'] === 'remove')
        {
            if(isset($_GET['action-confirmed']) && $_GET['action-confirmed'] === 'true')
            {
                $this->remove($user);
            }
            else
            {
                $this->createInterstitial();
                nel_clean_exit();
            }
        }
        else if ($inputs['action'] === 'lock')
        {
            $this->lock($user);
        }
        else if ($inputs['action'] === 'unlock')
        {
            $this->unlock($user);
        }

        $this->renderPanel($user);
    }

    public function renderPanel($user)
    {
        nel_render_manage_boards_panel($this->domain, $user);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        if (!$user->boardPerm('', 'perm_manage_boards_modify'))
        {
            nel_derp(371, _gettext('You are not allowed to modify boards.'));
        }

        $board_id = $_POST['new_board_id'];
        $board_directory = $_POST['board_directory'];
        $domain = new \Nelliel\DomainBoard($board_id, new \Nelliel\CacheHandler(), $this->database);
        $db_prefix = $domain->id();
        $prepared = $this->database->prepare(
                'INSERT INTO "' . BOARD_DATA_TABLE . '" ("board_id", "board_directory", "db_prefix") VALUES (?, ?, ?)');
        $this->database->executePrepared($prepared, [$domain->id(), $board_directory, $db_prefix]);
        $setup = new \Nelliel\Setup\Setup();
        $setup->createBoardTables($domain->id());
        $setup->createBoardDirectories($domain->id());
        $regen = new \Nelliel\Regen();

        if (USE_INTERNAL_CACHE)
        {
            $regen->boardCache($domain);
        }

        $regen->allPages($domain);
    }

    public function editor($user)
    {
    }

    public function update($user)
    {
    }

    public function remove($user)
    {
        if (!$user->boardPerm('', 'perm_manage_boards_modify'))
        {
            nel_derp(371, _gettext('You are not allowed to modify boards.'));
        }

        $board_id = $_GET['board_id'];
        $domain = new \Nelliel\DomainBoard($board_id, new \Nelliel\CacheHandler(), $this->database);

        if($this->database->tableExists($domain->reference('config_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('config_table'));
        }

        if($this->database->tableExists($domain->reference('content_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('content_table'));
        }

        if($this->database->tableExists($domain->reference('posts_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('posts_table'));
        }

        if($this->database->tableExists($domain->reference('threads_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('threads_table'));
        }

        if($this->database->tableExists($domain->reference('archive_content_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('archive_content_table'));
        }

        if($this->database->tableExists($domain->reference('archive_posts_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('archive_posts_table'));
        }

        if($this->database->tableExists($domain->reference('archive_threads_table')))
        {
            $this->database->query('DROP TABLE ' . $domain->reference('archive_threads_table'));
        }

        $file_handler = new \Nelliel\FileHandler();
        $file_handler->eraserGun($domain->reference('board_path'));
        $prepared = $this->database->prepare('DELETE FROM "' . BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
    }

    public function lock($user)
    {
        if (!$user->boardPerm('', 'perm_manage_boards_modify'))
        {
            nel_derp(371, _gettext('You are not allowed to modify boards.'));
        }

        $board_id = $_GET['board_id'];
        $prepared = $this->database->prepare('UPDATE "' . BOARD_DATA_TABLE . '" SET "locked" = 1 WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
    }

    public function unlock($user)
    {
        if (!$user->boardPerm('', 'perm_manage_boards_modify'))
        {
            nel_derp(371, _gettext('You are not allowed to modify boards.'));
        }

        $board_id = $_GET['board_id'];
        $prepared = $this->database->prepare('UPDATE "' . BOARD_DATA_TABLE . '" SET "locked" = 0 WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
    }

    public function createInterstitial()
    {
        $message = _gettext('Are you certain you want to delete the board? Everything will be gone and this cannot be undone!');
        $url_constructor = new \Nelliel\URLConstructor();
        $continue_link['href'] = $url_constructor->dynamic(MAIN_SCRIPT,
                ['module' => 'manage-boards', 'action' => 'remove', 'action-confirmed' => 'true',
                'board_id' => $_GET['board_id']]);
        $continue_link['text'] = _gettext('Confirm and delete the board.');
        nel_render_board_removal_interstitial($this->domain, $message, $continue_link);
    }
}
