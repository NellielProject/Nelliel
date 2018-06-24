<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'setup/insert_data.php';
require_once INCLUDE_PATH . 'setup/sql_tables.php';

//
// First run - checks for database, directories
// If anything does not exist yet, create it
//
function setup_check($board_id) // TODO Do this better
{
    if (SQLTYPE === 'MYSQL' && !nel_check_for_innodb())
    {
        nel_derp(202, nel_stext('InnoDB engine is required for MySQL support. However the engine has been disabled for some stupid reason.'));
    }

    nel_create_core_tables();
    nel_create_core_directories();

    if (true_empty($board_id))
    {
        return;
    }
}

function nel_setup_stuff_done($status = null)
{
    $stuff_done = false;

    if(!is_null($status) && $status !== false)
    {
        $stuff_done = true;
    }

    return $stuff_done;
}

function nel_create_core_directories()
{
    $file_handler = new \Nelliel\FileHandler();
    $file_handler->createDirectory(CACHE_PATH, DIRECTORY_PERM, true);
}

function nel_create_core_tables()
{
    // TODO: Remove passed table names
    nel_create_site_config_table(SITE_CONFIG_TABLE);
    nel_create_bans_table(BAN_TABLE);
    nel_create_user_table(USER_TABLE);
    nel_create_roles_table(ROLES_TABLE);
    nel_create_user_role_table(USER_ROLE_TABLE);
    nel_create_permissions_table(PERMISSIONS_TABLE);
    nel_create_logins_table(LOGINS_TABLE);
    nel_create_board_data_table(BOARD_DATA_TABLE);
    nel_create_filetype_table(FILETYPE_TABLE);
    nel_create_file_filter_table(FILE_FILTER_TABLE);
    nel_create_board_config_table(BOARD_DEFAULTS_TABLE, false);
}

function nel_create_board_directories($board_id)
{
    $file_handler = new \Nelliel\FileHandler();
    $references = nel_board_references($board_id);
    $file_handler->createDirectory($references['src_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['thumb_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['page_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['archive_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['archive_src_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['archive_thumb_path'], DIRECTORY_PERM, true);
    $file_handler->createDirectory($references['archive_page_path'], DIRECTORY_PERM, true);
}

function nel_create_board_tables($board_id)
{
    $references = nel_board_references($board_id);
    nel_create_threads_table($references['thread_table']);
    nel_create_threads_table($references['archive_thread_table']);
    nel_create_posts_table($references['post_table'], $references['thread_table']);
    nel_create_posts_table($references['archive_post_table'], $references['archive_thread_table']);
    nel_create_files_table($references['file_table'], $references['post_table']);
    nel_create_files_table($references['archive_file_table'], $references['archive_post_table']);
    nel_create_board_config_table($references['config_table'], true);
}

function nel_check_for_innodb()
{
    $dbh = nel_database();
    $result = $dbh->query("SHOW ENGINES");
    $list = $result->fetchAll(PDO::FETCH_ASSOC);

    foreach ($list as $entry)
    {
        if ($entry['Engine'] === 'InnoDB' && ($entry['Support'] === 'DEFAULT' || $entry['Support'] === 'YES'))
        {
            return true;
        }
    }

    return false;
}
