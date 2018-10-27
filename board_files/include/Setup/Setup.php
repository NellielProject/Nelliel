<?php

namespace Nelliel\Setup;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Setup
{

    function __construct()
    {
    }

    public function checkAll($board_id)
    {
        if ((SQLTYPE === 'MYSQL' || SQLTYPE === 'MARIADB') && !$this->checkForInnoDB())
        {
            nel_derp(102,
                    _gettext(
                            'InnoDB engine is required for MySQL or MariaDB support. However the engine is not available for some reason.'));
        }

        $this->createCoreTables();

        if (!is_writable(FILES_PATH))
        {
            nel_derp(104, _gettext('Board files directory is missing or not writable. Admin should check this out.'));
        }

        $this->createCoreDirectories();

        if ($board_id !== '')
        {
            $this->createBoardTables($board_id);
            $this->createBoardDirectories($board_id);
        }
    }

    public function createCoreTables()
    {
        $sql_tables = new SQLTables();
        $sql_tables->createSiteConfigTable(SITE_CONFIG_TABLE);
        $sql_tables->createBansTable(BAN_TABLE);
        $sql_tables->createUserTable(USER_TABLE);
        $sql_tables->createRolesTable(ROLES_TABLE);
        $sql_tables->createUserRoleTable(USER_ROLE_TABLE);
        $sql_tables->createPermissionsTable(PERMISSIONS_TABLE);
        $sql_tables->createLoginsTable(LOGINS_TABLE);
        $sql_tables->createBoardDataTable(BOARD_DATA_TABLE);
        $sql_tables->createFiletypeTable(FILETYPE_TABLE);
        $sql_tables->createFileFilterTable(FILE_FILTER_TABLE);
        $sql_tables->createBoardConfigTable(BOARD_DEFAULTS_TABLE, false);
        $sql_tables->createReportsTable(REPORTS_TABLE);
    }

    public function createCoreDirectories()
    {
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->createDirectory(CACHE_PATH, DIRECTORY_PERM, true);
    }

    public function createBoardTables($board_id)
    {
        $references = nel_parameters_and_data()->boardReferences($board_id);
        $sql_tables = new SQLTables();
        $sql_tables->createThreadsTable($references['thread_table']);
        $sql_tables->createThreadsTable($references['archive_thread_table']);
        $sql_tables->createPostsTable($references['post_table'], $references['thread_table']);
        $sql_tables->createPostsTable($references['archive_post_table'], $references['archive_thread_table']);
        $sql_tables->createFilesTable($references['file_table'], $references['post_table']);
        $sql_tables->createFilesTable($references['archive_file_table'], $references['archive_post_table']);
        $sql_tables->createBoardConfigTable($references['config_table'], true);
    }

    public function createBoardDirectories($board_id)
    {
        $file_handler = new \Nelliel\FileHandler();

        if (!is_writable(BASE_PATH))
        {
            nel_derp(105, _gettext('Nelliel main directory is not writable. Admin should check this out.'));
        }

        $references = nel_parameters_and_data()->boardReferences($board_id);
        $file_handler->createDirectory($references['src_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['thumb_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['page_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_src_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_thumb_path'], DIRECTORY_PERM, true);
        $file_handler->createDirectory($references['archive_page_path'], DIRECTORY_PERM, true);
    }

    private function checkForInnoDB()
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
}