<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TablePermissions extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = PERMISSIONS_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'permission' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'description' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function setup()
    {
        $this->createTable();
        $this->insertDefaults();
    }

    public function createTable(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            permission      VARCHAR(255) NOT NULL,
            description     VARCHAR(255) NOT NULL DEFAULT ''
        ) " . $options . ";";

        return $this->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['perm_site_config_access', 'Access the Site Settings panel']);
        $this->insertDefaultRow(['perm_site_config_modify', 'Modify site settings']);
        $this->insertDefaultRow(['perm_board_defaults_access', 'Access the Board Defaults panel']);
        $this->insertDefaultRow(['perm_board_defaults_modify', 'Modify board defaults']);
        $this->insertDefaultRow(['perm_board_config_access', 'Access the Board Settings panel']);
        $this->insertDefaultRow(['perm_board_config_modify', 'Modify board settings']);
        $this->insertDefaultRow(['perm_board_config_lock_override', 'Override board config lock']);
        $this->insertDefaultRow(['perm_user_access', 'Access the Users panel']);
        $this->insertDefaultRow(['perm_user_modify', 'Modify users']);
        $this->insertDefaultRow(['perm_role_access', 'Access the Roles panel']);
        $this->insertDefaultRow(['perm_role_modify', 'Modify roles']);
        $this->insertDefaultRow(['perm_ban_access', 'Access the Bans panel']);
        $this->insertDefaultRow(['perm_ban_modify', 'Modify bans']);
        $this->insertDefaultRow(['perm_modmode_access', 'Access to Moderator Mode']);
        $this->insertDefaultRow(['perm_threads_access', 'Access the Threads panel']);
        $this->insertDefaultRow(['perm_threads_modify', 'Modify threads and posts']);
        $this->insertDefaultRow(['perm_post_delete', 'Delete posts']);
        $this->insertDefaultRow(['perm_post_as_staff', 'Post as staff']);
        $this->insertDefaultRow(['perm_post_in_locked', 'Post in locked thread']);
        $this->insertDefaultRow(['perm_post_sticky', 'Sticky/unsticky posts and threads']);
        $this->insertDefaultRow(['perm_post_lock', 'Lock/unlock threads']);
        $this->insertDefaultRow(['perm_post_mod_comment', 'Add staff commentary to a post']);
        $this->insertDefaultRow(['perm_reports_access', 'Access the Reports panel']);
        $this->insertDefaultRow(['perm_reports_dismiss', 'Dismiss reports']);
        $this->insertDefaultRow(['perm_regen_cache', 'Regenerate caches']);
        $this->insertDefaultRow(['perm_regen_pages', 'Regenerate pages']);
        $this->insertDefaultRow(['perm_manage_boards_access', 'Access the Manage Boards panel']);
        $this->insertDefaultRow(['perm_manage_boards_modify', 'Modify boards']);
        $this->insertDefaultRow(['perm_extract_gettext', 'Extract Gettext strings']);
        $this->insertDefaultRow(['perm_file_filters_access', 'Access the File Filters panel']);
        $this->insertDefaultRow(['perm_file_filters_modify', 'Modify file filters']);
        $this->insertDefaultRow(['perm_templates_access', 'Access the Templates panel']);
        $this->insertDefaultRow(['perm_templates_modify', 'Modify templates']);
        $this->insertDefaultRow(['perm_filetypes_access', 'Access the Filetypes panel']);
        $this->insertDefaultRow(['perm_filetypes_modify', 'Modify filetypes']);
        $this->insertDefaultRow(['perm_styles_access', 'Access the Styles panel']);
        $this->insertDefaultRow(['perm_styles_modify', 'Modify styles']);
        $this->insertDefaultRow(['perm_permissions_access', 'Access the Permissions panel']);
        $this->insertDefaultRow(['perm_permissions_modify', 'Modify permissions']);
        $this->insertDefaultRow(['perm_news_access', 'Access the News panel']);
        $this->insertDefaultRow(['perm_news_modify', 'Modify news']);
    }
}