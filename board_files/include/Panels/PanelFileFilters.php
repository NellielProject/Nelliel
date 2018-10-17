<?php

namespace Nelliel\Panels;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/file_filter_panel.php';

class PanelFileFilters extends PanelBase
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
        }
        else if($inputs['action'] == 'remove')
        {
            $this->remove();
        }
        else
        {
            $this->renderPanel();
        }
    }

    public function renderPanel()
    {
        nel_render_file_filter_panel();
    }

    public function add()
    {
        $type = $_POST['hash_type'];
        $notes = $_POST['file_notes'];
        $output_filter = new \Nelliel\OutputFilter();
        $hashes = $output_filter->newlinesToArray($_POST['file_hashes']);

        foreach($hashes as $hash)
        {
            $prepared = $this->database->prepare('INSERT INTO "' . FILE_FILTER_TABLE . '" ("hash_type", "file_hash", "file_notes") VALUES (?, ?, ?)');
            $this->database->executePrepared($prepared, array($type, pack("H*" , $hash), $notes));
        }

        $this->renderPanel();
    }

    public function edit()
    {
    }

    public function update()
    {
    }

    public function remove()
    {
        $filter_id = $_GET['filter-id'];
        $prepared = $this->database->prepare('DELETE FROM "' . FILE_FILTER_TABLE . '" WHERE "entry" = ?');
        $this->database->executePrepared($prepared, array($filter_id));
        $this->renderPanel();
    }


}
