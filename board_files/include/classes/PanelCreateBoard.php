<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/create_board.php';

class PanelCreateBoard extends PanelBase
{
    function __construct($database, $authorize)
    {
        $this->database = $database;
        $this->authorize = $authorize;
    }

    public function actionDispatch($inputs)
    {

        if($inputs['action'] === 'add')
        {
            $this->add();
            $this->renderPanel();
        }
        else
        {
            $this->renderPanel();
        }
    }

    public function renderPanel()
    {
        nel_render_create_board_panel();
    }

    public function add()
    {
        $user = $this->authorize->getUser($_SESSION['username']);

        if (!$user->boardPerm('', 'perm_create_board'))
        {
            nel_derp(370, _gettext('You are not allowed to create new boards.'));
        }
        $board_id = $_POST['new_board_id'];
        $board_directory = $_POST['board_directory'];
        $db_prefix = $board_id;
        $prepared = $this->database->prepare('INSERT INTO "' . BOARD_DATA_TABLE . '" ("board_id", "board_directory", "db_prefix") VALUES (?, ?, ?)');
        $this->database->executePrepared($prepared, array($board_id, $board_directory, $db_prefix));
        $setup = new \Nelliel\setup\Setup();
        $setup->createBoardTables($board_id);
        $setup->createBoardDirectories($board_id);

        if(USE_INTERNAL_CACHE)
        {
            $regen = new \Nelliel\Regen();
            $regen->boardCache($board_id);
        }
    }

    public function edit()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
    }


}
