<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'setup/sql_tables.php';

//
// First run - checks for database, directories
// If anything does not exist yet, create it
//

function setup_check($board_id)    // TODO Do this better
{
    $file_handler = nel_file_handler();

    nel_create_bans_table(BAN_TABLE);
    nel_create_user_table(USER_TABLE);
    nel_create_roles_table(ROLES_TABLE);
    nel_create_user_role_table(USER_ROLE_TABLE);
    nel_create_permissions_table(PERMISSIONS_TABLE);
    nel_create_logins_table(LOGINS_TABLE);
    nel_create_board_data_table(BOARD_DATA_TABLE);
    nel_create_site_config_table(SITE_CONFIG_TABLE);

    $file_handler->createDirectory(CACHE_PATH, DIRECTORY_PERM, true);

    if(true_empty($board_id))
    {
        return;
    }

    $references = nel_board_references($board_id);

    nel_create_threads_table($references['thread_table']);
    nel_create_threads_table($references['archive_thread_table']);
    nel_create_posts_table($references['post_table'], $references['thread_table']);
    nel_create_posts_table($references['archive_post_table'], $references['archive_thread_table']);
    nel_create_files_table($references['file_table'], $references['post_table']);
    nel_create_files_table($references['archive_file_table'], $references['archive_post_table']);
    nel_create_external_table($references['external_table'], $references['post_table']);
    nel_create_external_table($references['archive_external_table'], $references['archive_post_table']);
    nel_create_board_config_table($references['config_table']);

    $file_handler->createDirectory($references['src_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['thumb_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['page_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['archive_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['archive_src_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['archive_thumb_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['archive_page_path'], DIRECTORY_PERM, true);

    nel_setup_stuff_done('check_done_nochange');

    if (STUFF_DONE === true)
    {
        // This should go to a log or something.
        //echo '<br><br>Process completed. If there are no errors listed above then you did it right. Please wait a few seconds and you will be taken to the front page.';
    }
}

function nel_setup_stuff_done($status)
{
    if (!defined('STUFF_DONE'))
    {
        if ($status === 'check_done_nochange')
        {
            define('STUFF_DONE', false);
        }
        else if (!$status !== false)
        {
            define('STUFF_DONE', true);
        }
    }
}
