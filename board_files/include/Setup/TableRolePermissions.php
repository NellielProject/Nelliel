<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableRolePermissions extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = ROLE_PERMISSIONS_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'role_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'perm_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'perm_setting' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function setup()
    {
        $this->createTable();
        $this->insertDefaults();
    }

    public function createTable(array $other_tables = null)
    {
        $auto_inc = $this->sql_helpers->autoincrementColumn('INTEGER');
        $options = $this->sql_helpers->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            role_id         VARCHAR(255) NOT NULL,
            perm_id         VARCHAR(255) NOT NULL,
            perm_setting    SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_site_config_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_site_config_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_board_defaults_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_board_defaults_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_board_config_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_board_config_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_board_config_lock_override', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_user_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_user_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_role_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_role_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_ban_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_ban_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_modmode_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_threads_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_post_delete', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_post_as_staff', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_post_in_locked', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_post_sticky', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_post_lock', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_post_mod_comment', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_reports_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_reports_dismiss', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_regen_cache', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_regen_pages', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_manage_boards_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_manage_boards_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_extract_gettext', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_file_filters_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_file_filters_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_templates_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_templates_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_filetypes_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_filetypes_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_styles_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_styles_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_permissions_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_permissions_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_icon_sets_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_icon_sets_modify', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_news_access', 1]);
        $this->insertDefaultRow(['SUPER_ADMIN', 'perm_news_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_site_config_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_site_config_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_defaults_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_defaults_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_config_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_config_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_config_lock_override', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_user_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_user_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_role_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_role_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_ban_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_ban_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_modmode_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_threads_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_post_delete', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_post_as_staff', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_post_in_locked', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_post_sticky', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_post_lock', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_post_mod_comment', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_reports_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_reports_dismiss', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_regen_cache', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_regen_pages', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_boards_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_boards_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_extract_gettext', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_file_filters_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_file_filters_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_templates_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_templates_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_filetypes_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_filetypes_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_styles_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_styles_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_permissions_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_permissions_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_icon_sets_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_icon_sets_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_news_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_news_modify', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_site_config_access', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_site_config_modify', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_board_defaults_access', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_board_defaults_modify', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_board_config_access', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_board_config_modify', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_board_config_lock_override', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_user_access', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_user_modify', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_role_access', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_role_modify', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_ban_access', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_ban_modify', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_modmode_access', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_threads_modify', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_post_delete', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_post_as_staff', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_post_in_locked', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_post_sticky', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_post_lock', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_post_mod_comment', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_reports_access', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_reports_dismiss', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_regen_cache', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_regen_pages', 1]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_manage_boards_access', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_manage_boards_modify', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_file_filters_access', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_file_filters_modify', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_templates_access', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_templates_modify', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_filetypes_access', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_filetypes_modify', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_styles_access', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_styles_modify', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_permissions_access', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_permissions_modify', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_icon_sets_access', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_icon_sets_modify', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_news_access', 0]);
        $this->insertDefaultRow(['BOARD_ADMIN', 'perm_news_modify', 0]);
        $this->insertDefaultRow(['MOD', 'perm_site_config_access', 0]);
        $this->insertDefaultRow(['MOD', 'perm_site_config_modify', 0]);
        $this->insertDefaultRow(['MOD', 'perm_board_defaults_access', 0]);
        $this->insertDefaultRow(['MOD', 'perm_board_defaults_modify', 0]);
        $this->insertDefaultRow(['MOD', 'perm_board_config_access', 0]);
        $this->insertDefaultRow(['MOD', 'perm_board_config_modify', 0]);
        $this->insertDefaultRow(['MOD', 'perm_board_config_lock_override', 0]);
        $this->insertDefaultRow(['MOD', 'perm_user_access', 0]);
        $this->insertDefaultRow(['MOD', 'perm_user_modify', 0]);
        $this->insertDefaultRow(['MOD', 'perm_role_access', 0]);
        $this->insertDefaultRow(['MOD', 'perm_role_modify', 0]);
        $this->insertDefaultRow(['MOD', 'perm_ban_access', 1]);
        $this->insertDefaultRow(['MOD', 'perm_ban_modify', 1]);
        $this->insertDefaultRow(['MOD', 'perm_modmode_access', 1]);
        $this->insertDefaultRow(['MOD', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['MOD', 'perm_threads_modify', 1]);
        $this->insertDefaultRow(['MOD', 'perm_post_delete', 1]);
        $this->insertDefaultRow(['MOD', 'perm_post_as_staff', 1]);
        $this->insertDefaultRow(['MOD', 'perm_post_in_locked', 1]);
        $this->insertDefaultRow(['MOD', 'perm_post_sticky', 1]);
        $this->insertDefaultRow(['MOD', 'perm_post_lock', 1]);
        $this->insertDefaultRow(['MOD', 'perm_post_mod_comment', 1]);
        $this->insertDefaultRow(['MOD', 'perm_reports_access', 1]);
        $this->insertDefaultRow(['MOD', 'perm_reports_dismiss', 1]);
        $this->insertDefaultRow(['MOD', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['MOD', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['MOD', 'perm_manage_boards_access', 0]);
        $this->insertDefaultRow(['MOD', 'perm_manage_boards_modify', 0]);
        $this->insertDefaultRow(['MOD', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['MOD', 'perm_file_filters_access', 0]);
        $this->insertDefaultRow(['MOD', 'perm_file_filters_modify', 0]);
        $this->insertDefaultRow(['MOD', 'perm_templates_access', 0]);
        $this->insertDefaultRow(['MOD', 'perm_templates_modify', 0]);
        $this->insertDefaultRow(['MOD', 'perm_filetypes_access', 0]);
        $this->insertDefaultRow(['MOD', 'perm_filetypes_modify', 0]);
        $this->insertDefaultRow(['MOD', 'perm_styles_access', 0]);
        $this->insertDefaultRow(['MOD', 'perm_styles_modify', 0]);
        $this->insertDefaultRow(['MOD', 'perm_permissions_access', 0]);
        $this->insertDefaultRow(['MOD', 'perm_permissions_modify', 0]);
        $this->insertDefaultRow(['MOD', 'perm_icon_sets_access', 0]);
        $this->insertDefaultRow(['MOD', 'perm_icon_sets_modify', 0]);
        $this->insertDefaultRow(['MOD', 'perm_news_access', 0]);
        $this->insertDefaultRow(['MOD', 'perm_news_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_site_config_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_site_config_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_defaults_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_defaults_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_config_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_config_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_config_lock_override', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_user_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_user_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_role_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_role_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_ban_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_ban_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_modmode_access', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_threads_modify', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_post_delete', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_post_as_staff', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_post_in_locked', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_post_sticky', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_post_lock', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_post_mod_comment', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_reports_access', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_reports_dismiss', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_boards_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_boards_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_file_filters_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_file_filters_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_templates_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_templates_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_filetypes_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_filetypes_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_styles_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_styles_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_permissions_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_permissions_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_icon_sets_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_icon_sets_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_news_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_news_modify', 0]);
    }
}