<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'setup/sql-tables.php';

//
// First run - checks for database, directories
// If anything does not exist yet, create it
//
function nel_create_structure_directory($path, $directory, $perms)
{
    if (file_exists($path))
    {
        return;
    }

    echo 'Creating directory ' . $directory . '<br>';

    if (mkdir($path, $perms))
    {
        ;
    }
    else
    {
        die('Could not create ' . $directory .
             ' directory. Check permissions and config.php settings then retry installation.');
    }
}

function setup_check()
{
    $dbh = nel_get_db_handle();
    nel_create_posts_table(POST_TABLE, true);
    nel_create_posts_table(ARCHIVE_POST_TABLE, false);
    nel_create_threads_table(THREAD_TABLE, false);
    nel_create_threads_table(ARCHIVE_THREAD_TABLE, false);
    nel_create_files_table(FILE_TABLE, false);
    nel_create_files_table(ARCHIVE_FILE_TABLE, false);
    nel_create_external_table(EXTERNAL_TABLE, false);
    nel_create_external_table(ARCHIVE_EXTERNAL_TABLE, false);
    nel_create_bans_table(BAN_TABLE, true);
    nel_create_config_table(CONFIG_TABLE, false);

    nel_create_structure_directory(SRC_PATH, SRC_DIR, 0755);
    nel_create_structure_directory(THUMB_PATH, THUMB_DIR, 0755);
    nel_create_structure_directory(PAGE_PATH, PAGE_DIR, 0755);
    nel_create_structure_directory(CACHE_PATH, CACHE_DIR, 0755);
    nel_create_structure_directory(ARCHIVE_PATH, ARCHIVE_DIR, 0755);
    nel_create_structure_directory(ARC_SRC_PATH, ARCHIVE_DIR . SRC_DIR, 0755);
    nel_create_structure_directory(ARC_THUMB_PATH, ARCHIVE_DIR . THUMB_DIR, 0755);
    nel_create_structure_directory(ARC_PAGE_PATH, ARCHIVE_DIR . PAGE_DIR, 0755);

    if ($stuff_done)
    {
        define('STUFF_DONE', TRUE);
        echo '<br><br>Process completed. If there are no errors listed above then you did it right. Please wait a few seconds and you will be taken to the front page.';
    }
    else
    {
        define('STUFF_DONE', FALSE);
    }
}

function generate_auth_file()
{
    if (!file_exists(FILES_PATH . '/auth_data.nel.php'))
    {
        if (DEFAULTADMIN !== '' && DEFAULTADMIN_PASS !== '')
        {
            echo 'Creating auth file...';
            $new_auth = '<?php
$authorized = array(
    \'' . DEFAULTADMIN . '\' => array(
        \'settings\' => array(
            \'staff_password\' => \'' .
                 nel_password_hash(DEFAULTADMIN_PASS, NELLIEL_PASS_ALGORITHM) . '\',
            \'staff_type\' => \'admin\',
            \'staff_trip\' => \'\'),
        \'perms\' => array(
            \'perm_config\' => TRUE,
            \'perm_staff_panel\' => TRUE,
            \'perm_ban_panel\' => TRUE,
            \'perm_thread_panel\' => TRUE,
            \'perm_mod_mode\' => TRUE,
            \'perm_ban\' => TRUE,
            \'perm_delete\' => TRUE,
            \'perm_post\' => TRUE,
            \'perm_post_anon\' => TRUE,
            \'perm_sticky\' => TRUE,
            \'perm_update_pages\' => TRUE,
            \'perm_update_cache\' => TRUE
        )),
    ); ?>';

            if (nel_write_file(FILES_PATH . 'auth_data.nel.php', $new_auth, 0644))
            {
                $stuff_done = TRUE;
            }
            else
            {
                die('Could not create auth file. Check permissions and config.php then retry installation.');
            }
        }
        else
        {
            $stuff_done = TRUE;
            echo 'ERROR: Could not create auth file due to invalid or missing admin info. The board will probably work but you will have no administrative abilities. Check your config.php then retry installation.';
        }
    }
}
?>
