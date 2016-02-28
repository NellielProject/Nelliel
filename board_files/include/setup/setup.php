<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// First run - checks for database, directories
// If anything does not exist yet, create it
//

function setup_check()
{
    $dbh = nel_get_db_handle();
    $stuff_done = FALSE;
    $tables = array(
    POST_TABLE => FALSE,
    THREAD_TABLE => FALSE,
    FILE_TABLE => FALSE,
    EXTERNAL_TABLE => FALSE,
    ARCHIVE_POST_TABLE => FALSE,
    ARCHIVE_THREAD_TABLE => FALSE,
    ARCHIVE_FILE_TABLE => FALSE,
    ARCHIVE_EXTERNAL_TABLE => FALSE,
    CONFIG_TABLE => FALSE,
    BAN_TABLE => FALSE);

    $tables = get_tables($dbh, $tables);

    if (SQLTYPE === 'MYSQL')
    {
        require_once INCLUDE_PATH . '/setup/mysql-tables.php';
    }
    else if (SQLTYPE === 'SQLITE')
    {
        require_once INCLUDE_PATH . '/setup/sqlite-tables.php';
    }

    nel_create_post_table($dbh, $tables);
    nel_create_thread_table($dbh, $tables);
    nel_create_file_table($dbh, $tables);
    nel_create_external_content_table($dbh, $tables);
    nel_create_archive_post_table($dbh, $tables);
    nel_create_archive_thread_table($dbh, $tables);
    nel_create_archive_file_table($dbh, $tables);
    nel_create_archive_external_content_table($dbh, $tables);
    nel_create_config_table($dbh, $tables);
    nel_create_ban_table($dbh, $tables);

    if (!file_exists(SRC_PATH))
    {
        echo 'Creating directory ' . SRC_DIR . '<br>';
        if (mkdir(SRC_PATH, 0755))
        {
            chmod(SRC_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . SRC_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(THUMB_PATH))
    {
        echo 'Creating directory ' . THUMB_DIR . '<br>';
        if (mkdir(THUMB_PATH, 0755))
        {
            chmod(THUMB_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . THUMB_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(PAGE_PATH))
    {
        echo 'Creating directory ' . PAGE_DIR . '<br>';
        if (mkdir(PAGE_PATH, 0755))
        {
            chmod(PAGE_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . PAGE_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(CACHE_PATH))
    {
        echo 'Creating directory ' . CACHE_DIR . '<br>';
        if (mkdir(CACHE_PATH, 0755))
        {
            chmod(CACHE_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . CACHE_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(ARCHIVE_PATH))
    {
        echo 'Creating directory ' . ARCHIVE_DIR . '<br>';
        if (mkdir(ARCHIVE_PATH, 0755))
        {
            chmod(ARCHIVE_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . ARCHIVE_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(ARC_SRC_PATH))
    {
        echo 'Creating directory ' . ARCHIVE_DIR . SRC_DIR . '<br>';
        if (mkdir(ARC_SRC_PATH, 0755))
        {
            chmod(ARC_SRC_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . ARCHIVE_DIR . SRC_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(ARC_THUMB_PATH))
    {
        echo 'Creating directory ' . ARCHIVE_DIR . THUMB_DIR . '<br>';
        if (mkdir(ARC_THUMB_PATH, 0755))
        {
            chmod(ARC_THUMB_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . ARCHIVE_DIR . THUMB_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(ARC_PAGE_PATH))
    {
        echo 'Creating directory ' . ARCHIVE_DIR . PAGE_DIR . '<br>';
        if (mkdir(ARC_PAGE_PATH, 0755))
        {
            chmod(ARC_PAGE_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . ARCHIVE_DIR . PAGE_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

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

function nel_table_fail($table)
{
    die('Creation of ' . $table . ' failed! Check database settings and config.php then retry installation.');
}

function table_exists($table, $dbh)
{
    if (SQLTYPE === 'SQLITE')
    {
        $result = $dbh->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name='" . $table . "'");
    }

    if (SQLTYPE === 'MYSQL')
    {
        $result = $dbh->query("SHOW TABLES FROM `" . SQLDB . "` LIKE '" . $table . "'");
    }

    $test = $result->fetch(PDO::FETCH_NUM);

    if ($test[0] == $table)
    {
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

function check_engines($dbh, $engine)
{
    static $engines;

    if (!isset($engines))
    {
        $result = $dbh->query("SHOW ENGINES");
        $list = $result->fetchAll(PDO::FETCH_ASSOC);

        foreach ($list as $entry)
        {
            if ($entry['Support'] === 'DEFAULT' || $entry['Support'] === 'YES')
            {
                $engines[$entry['Engine']] = TRUE;
            }
        }
    }

    if (array_key_exists($engine, $engines))
    {
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

function get_tables($dbh, $tables)
{
    if (SQLTYPE === 'SQLITE')
    {
        $result = $dbh->query("SELECT name FROM sqlite_master WHERE type = 'table'");
    }

    if (SQLTYPE === 'MYSQL')
    {
        $result = $dbh->query("select table_name from information_schema.tables where table_schema = '" . MYSQL_DB . "'");
    }

    $table_list = $result->fetchAll(PDO::FETCH_COLUMN);

    foreach ($table_list as $table)
    {
        $tables[$table] = TRUE;
    }

    return $tables;
}

function generate_auth_file($plugins)
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
            \'staff_password\' => \'' . nel_hash(DEFAULTADMIN_PASS, $plugins) . '\',
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
