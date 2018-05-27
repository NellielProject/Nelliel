<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_manage_file_filters($action)
{
    $dbh = nel_database();

    if($action === 'add')
    {
        $type = $_POST['hash_type'];
        $output_filter = new \Nelliel\OutputFilter();
        $hashes = $output_filter->newlinesToArray($_POST['file_hashes']);

        foreach($hashes as $hash)
        {
            $prepared = $dbh->prepare('INSERT INTO "' . FILE_FILTER_TABLE . '" ("hash_type", "file_hash") VALUES (?, ?)');
            $dbh->executePrepared($prepared, array($type, pack("H*" , $hash)));
        }
    }
}