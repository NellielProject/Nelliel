<?php

namespace Nelliel\Panels;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/manage_boards.php';

class PanelManageBoards extends PanelBase
{

    function __construct($database, $authorize)
    {
        $this->database = $database;
        $this->authorize = $authorize;
    }

    public function actionDispatch($inputs)
    {
        $user = $this->authorize->getUser($_SESSION['username']);

        if ($inputs['action'] === 'add')
        {
            $this->add($user);
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
        $this->database->executePrepared($prepared, array($board_id, $board_directory, $db_prefix));
        $setup = new \Nelliel\setup\Setup();
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
    }
}
