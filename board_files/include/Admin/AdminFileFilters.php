<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/file_filter_panel.php';

class AdminFileFilters extends AdminBase
{
    private $board;

    function __construct($database, $authorization, $board)
    {
        $this->database = $database;
        $this->authorization = $authorization;
        $this->board = $board;
    }

    public function actionDispatch($inputs)
    {
        $session = new \Nelliel\Session($this->authorization, true);
        $user = $session->sessionUser();

        if($inputs['action'] === 'add')
        {
            $this->add($user);
        }
        else if($inputs['action'] == 'remove')
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
        nel_render_file_filter_panel($user, $this->board);
    }

    public function creator($user)
    {
    }

    public function add($user)
    {
        if (!$user->boardPerm('', 'perm_file_filters_add'))
        {
            nel_derp(341, _gettext('You are not allowed to add file filters.'));
        }

        $type = $_POST['hash_type'];
        $notes = $_POST['file_notes'];
        $board_id = $_POST['board_id'];
        $output_filter = new \Nelliel\OutputFilter();
        $hashes = $output_filter->newlinesToArray($_POST['file_hashes']);

        foreach($hashes as $hash)
        {
            $prepared = $this->database->prepare('INSERT INTO "' . FILE_FILTER_TABLE . '" ("hash_type", "file_hash", "file_notes", "board_id") VALUES (?, ?, ?, ?)');
            $this->database->executePrepared($prepared, [$type, pack("H*" , $hash), $notes, $board_id]);
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
        if (!$user->boardPerm($this->board->id(), 'perm_file_filters_delete'))
        {
            nel_derp(342, _gettext('You are not allowed to remove file filters.'));
        }

        $filter_id = $_GET['filter-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . FILE_FILTER_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, array($filter_id));
        $this->renderPanel($user);
    }


}
