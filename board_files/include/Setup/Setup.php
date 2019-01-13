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

    public function generateConfigValues($current_values = null)
    {
        $generated = $generated ?? array();
        $generated['tripcode_pepper'] = $generated['tripcode_pepper'] ?? base64_encode(random_bytes(32));
        return $generated;
    }

    public function checkGenerated()
    {
        if (!file_exists(CONFIG_FILE_PATH . 'generated.php'))
        {
            $file_handler = new \Nelliel\FileHandler();
            $generated = $this->generateConfigValues();
            $prepend = "\n" . '// DO NOT EDIT THESE VALUES OR REMOVE THIS FILE UNLESS YOU HAVE A DAMN GOOD REASON';
            $file_handler->writeInternalFile(CONFIG_FILE_PATH . 'generated.php',
                    $prepend . "\n" . '$generated = ' . var_export($generated, true) . ';');
        }
    }

    public function checkAll($board_id)
    {
        $this->checkGenerated();

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
        $sql_tables->createRolePermissionsTable(ROLE_PERMISSIONS_TABLE);
        $sql_tables->createPermissionsTable(PERMISSIONS_TABLE);
        $sql_tables->createLoginsTable(LOGINS_TABLE);
        $sql_tables->createBoardDataTable(BOARD_DATA_TABLE);
        $sql_tables->createFiletypeTable(FILETYPE_TABLE);
        $sql_tables->createFileFilterTable(FILE_FILTER_TABLE);
        $sql_tables->createBoardConfigTable(BOARD_DEFAULTS_TABLE, false);
        $sql_tables->createReportsTable(REPORTS_TABLE);
        $sql_tables->createAssetsTable(ASSETS_TABLE);
        $sql_tables->createCaptchaTable(CAPTCHA_TABLE);
        $sql_tables->createVersionTable(VERSION_TABLE);
    }

    public function createCoreDirectories()
    {
        $file_handler = new \Nelliel\FileHandler();
        $file_handler->createDirectory(CACHE_FILE_PATH, DIRECTORY_PERM, true);
    }

    public function createBoardTables($board_id)
    {
        $board_references = nel_parameters_and_data()->boardReferences($board_id);
        $sql_tables = new SQLTables();
        $sql_tables->createThreadsTable($board_references['thread_table']);
        $sql_tables->createThreadsTable($board_references['archive_thread_table']);
        $sql_tables->createPostsTable($board_references['post_table'], $board_references['thread_table']);
        $sql_tables->createPostsTable($board_references['archive_post_table'], $board_references['archive_thread_table']);
        $sql_tables->createContentTable($board_references['content_table'], $board_references['post_table']);
        $sql_tables->createContentTable($board_references['archive_content_table'],
                $board_references['archive_post_table']);
        $sql_tables->createBoardConfigTable($board_references['config_table'], true);
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
        $database = nel_database();
        $result = $database->query("SHOW ENGINES");
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