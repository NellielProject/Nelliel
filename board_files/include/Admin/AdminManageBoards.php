<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/manage_boards.php';

use PDO;

class AdminManageBoards extends AdminBase
{

    function __construct($database, $authorization)
    {
        $this->database = $database;
        $this->authorization = $authorization;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session($this->authorization, true);
        $user = $session->sessionUser();

        if ($inputs['action'] === 'add')
        {
            $this->add($user);
            $this->renderPanel($user);
        }
        else if ($inputs['action'] === 'remove')
        {
            $this->remove($user);
            $this->renderPanel($user);
        }
        else
        {
            $this->renderPanel($user);
        }
    }

    public function renderPanel($user)
    {
        nel_render_manage_boards_panel($user);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        if (!$user->boardPerm('', 'perm_manage_boards_add'))
        {
            nel_derp(371, _gettext('You are not allowed to create new boards.'));
        }

        $board_id = $_POST['new_board_id'];
        $board_directory = $_POST['board_directory'];
        $db_prefix = $board_id;
        $prepared = $this->database->prepare(
                'INSERT INTO "' . BOARD_DATA_TABLE . '" ("board_id", "board_directory", "db_prefix") VALUES (?, ?, ?)');
        $this->database->executePrepared($prepared, [$board_id, $board_directory, $db_prefix]);
        $setup = new \Nelliel\Setup\Setup();
        $setup->createBoardTables($board_id);
        $setup->createBoardDirectories($board_id);

        if (USE_INTERNAL_CACHE)
        {
            $regen = new \Nelliel\Regen();
            $regen->boardCache($board_id);
        }
    }

    public function editor($user)
    {
    }

    public function update($user)
    {
    }

    public function remove($user)
    {
        if (!$user->boardPerm('', 'perm_manage_boards_delete'))
        {
            nel_derp(372, _gettext('You are not allowed to create new boards.'));
        }

        $board_id = $_GET['board_id'];
        $board_references = nel_parameters_and_data()->boardReferences($board_id);
        $prepared = $this->database->prepare('SELECT * FROM "' . BOARD_DATA_TABLE . '" WHERE "board_id" = ? LIMIT 1');
        $board_data = $this->database->executePreparedFetch($prepared, [$board_id], PDO::FETCH_ASSOC);
        $prepared = $this->database->prepare('DELETE FROM "' . BOARD_DATA_TABLE . '" WHERE "board_id" = ?');
        $this->database->executePrepared($prepared, [$board_id]);
        $this->database->query('DROP TABLE ' . $board_data['db_prefix'] . '_config');
        $this->database->query('DROP TABLE ' . $board_data['db_prefix'] . '_files');
        $this->database->query('DROP TABLE ' . $board_data['db_prefix'] . '_posts');
        $this->database->query('DROP TABLE ' . $board_data['db_prefix'] . '_threads');
        $this->database->query('DROP TABLE ' . $board_data['db_prefix'] . '_archive_files');
        $this->database->query('DROP TABLE ' . $board_data['db_prefix'] . '_archive_posts');
        $this->database->query('DROP TABLE ' . $board_data['db_prefix'] . '_archive_threads');

        $file_handler = new \Nelliel\FileHandler();
        $file_handler->eraserGun($board_references['board_path'], null, true);
    }
}
