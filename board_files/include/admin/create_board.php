<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_create_new_board()
{
    $dbh = nel_database();
    $file_handler = nel_file_handler();
    $board_id = $_POST['new_board_id'];
    $board_directory = $_POST['board_directory'];
    $db_prefix = $board_id;

    $prepared = $dbh->prepare('INSERT INTO "' . BOARD_DATA_TABLE . '" ("board_id", "board_directory", "db_prefix") VALUES (?, ?, ?)');
    $dbh->executePrepared($prepared, array($board_id, $board_directory, $db_prefix));
    $references = nel_board_references($board_id);

    nel_create_posts_table($references['post_table']);
    nel_create_posts_table($references['archive_post_table']);
    nel_create_threads_table($references['thread_table']);
    nel_create_threads_table($references['archive_thread_table']);
    nel_create_files_table($references['file_table']);
    nel_create_files_table($references['archive_file_table']);
    nel_create_external_table($references['external_table']);
    nel_create_external_table($references['archive_external_table']);
    nel_create_config_table($references['config_table']);

    $file_handler->createDirectory($references['src_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['thumb_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['page_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['archive_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['archive_src_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['archive_thumb_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['archive_page_path'], DIRECTORY_PERM, true);

    return $board_id;
}