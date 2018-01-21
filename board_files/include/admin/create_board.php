<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_create_new_board()
{
    $dbh = nel_database();
    $file_handler = nel_file_handler();

    $board_id = $_POST['board_id'];
    $board_directory = $_POST['board_directory'];
    $db_prefix = $board_id;

    nel_create_posts_table($db_prefix . '_posts');
    nel_create_posts_table($db_prefix . '_archive_posts');
    nel_create_threads_table($db_prefix . '_threads');
    nel_create_threads_table($db_prefix . '_archive_threads');
    nel_create_files_table($db_prefix . '_files');
    nel_create_files_table($db_prefix . '_archive_files');
    nel_create_external_table($db_prefix . '_external');
    nel_create_external_table($db_prefix . '_archive_external');
    nel_create_config_table($db_prefix . '_config');
    $prepared = $dbh->prepare('INSERT INTO "' . BOARD_DATA_TABLE . '" ("board_id", "board_directory", "db_prefix") VALUES (?, ?, ?)');
    $dbh->executePrepared($prepared, array($board_id, $board_directory, $db_prefix));

    $board_path = BASE_PATH . $board_directory . '/';
    $archive_path = $board_path . ARCHIVE_DIR;

    $file_handler->createDirectory($board_path, DIRECTORY_PERM);
    $file_handler->createDirectory($board_path . SRC_DIR, DIRECTORY_PERM);
    $file_handler->createDirectory($board_path . THUMB_DIR, DIRECTORY_PERM);
    $file_handler->createDirectory($board_path . PAGE_DIR, DIRECTORY_PERM);
    $file_handler->createDirectory($archive_path, DIRECTORY_PERM);
    $file_handler->createDirectory($archive_path. SRC_DIR, DIRECTORY_PERM);
    $file_handler->createDirectory($archive_path. THUMB_DIR, DIRECTORY_PERM);
    $file_handler->createDirectory($archive_path. PAGE_DIR, DIRECTORY_PERM);
}