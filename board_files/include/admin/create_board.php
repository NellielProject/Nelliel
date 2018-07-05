<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_create_new_board()
{
    $dbh = nel_database();
    $board_id = $_POST['new_board_id'];
    $board_directory = $_POST['board_directory'];
    $db_prefix = $board_id;
    $prepared = $dbh->prepare('INSERT INTO "' . BOARD_DATA_TABLE . '" ("board_id", "board_directory", "db_prefix") VALUES (?, ?, ?)');
    $dbh->executePrepared($prepared, array($board_id, $board_directory, $db_prefix));
    nel_create_board_tables($board_id);
    nel_create_board_directories($board_id);

    if(USE_INTERNAL_CACHE)
    {
        $regen = new \Nelliel\Regen();
        $regen->boardCache($board_id);
    }

    return $board_id;
}