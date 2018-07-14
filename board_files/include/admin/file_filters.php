<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/management/file_filter_panel.php';

function nel_manage_file_filters($inputs)
{
    $dbh = nel_database();

    if($inputs['action'] === 'add')
    {
        $type = $_POST['hash_type'];
        $notes = $_POST['file_notes'];
        $output_filter = new \Nelliel\OutputFilter();
        $hashes = $output_filter->newlinesToArray($_POST['file_hashes']);

        foreach($hashes as $hash)
        {
            $prepared = $dbh->prepare('INSERT INTO "' . FILE_FILTER_TABLE . '" ("hash_type", "file_hash", "file_notes") VALUES (?, ?, ?)');
            $dbh->executePrepared($prepared, array($type, pack("H*" , $hash), $notes));
        }
    }
    else if($inputs['action'] === 'remove')
    {
        $filter_id = $_POST['filter_id'];
        $prepared = $dbh->prepare('DELETE FROM "' . FILE_FILTER_TABLE . '" WHERE "entry" = ?');
        $dbh->executePrepared($prepared, array($filter_id));
    }

    nel_render_file_filter_panel();
}