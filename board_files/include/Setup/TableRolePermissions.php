<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableRolePermissions extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
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
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            role_id         VARCHAR(255) NOT NULL,
            perm_id         VARCHAR(255) NOT NULL,
            perm_setting    SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $this->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['SITE_OWNER', 'perm_site_config', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_manage_templates', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_manage_styles', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_manage_icon_sets', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_manage_users', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_manage_roles', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_manage_filetypes', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_extract_gettext', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_defaults', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_config_lock_override', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_manage_news', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_manage_file_filters', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_regen_cache', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_regen_pages', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_assign_role', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_create', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_delete', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_transfer', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_lock', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_config', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_manage_bans', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_sticky_posts', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_lock_posts', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_post_in_locked', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_post_as_staff', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_mod_comment', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_modify_posts', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_manage_reports', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['SITE_OWNER', 'perm_board_delete_posts', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_site_config', 0]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_templates', 0]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_styles', 0]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_icon_sets', 0]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_users', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_roles', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_filetypes', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_extract_gettext', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_defaults', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_config_lock_override', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_news', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_file_filters', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_regen_cache', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_regen_pages', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_assign_role', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_create', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_delete', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_transfer', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_lock', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_config', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_bans', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_sticky_posts', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_lock_posts', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_post_in_locked', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_post_as_staff', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_mod_comment', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_modify_posts', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_reports', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_delete_posts', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_site_config', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_templates', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_styles', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_icon_sets', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_users', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_roles', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_filetypes', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_defaults', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_config_lock_override', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_news', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_file_filters', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_regen_cache', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_regen_pages', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_assign_role', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_create', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_delete', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_transfer', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_lock', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_config', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_bans', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_sticky_posts', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_lock_posts', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_post_in_locked', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_post_as_staff', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_mod_comment', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_modify_posts', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_reports', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_delete_posts', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_site_config', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_templates', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_styles', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_icon_sets', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_users', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_roles', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_filetypes', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_defaults', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_config_lock_override', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_news', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_file_filters', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_assign_role', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_create', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_delete', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_transfer', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_lock', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_config', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_bans', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_sticky_posts', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_lock_posts', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_post_in_locked', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_post_as_staff', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_mod_comment', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_modify_posts', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_reports', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_delete_posts', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_site_config', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_templates', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_styles', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_icon_sets', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_users', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_roles', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_filetypes', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_defaults', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_config_lock_override', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_news', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_file_filters', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_assign_role', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_create', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_delete', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_transfer', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_lock', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_config', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_bans', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_sticky_posts', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_lock_posts', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_post_in_locked', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_post_as_staff', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_mod_comment', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_modify_posts', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_reports', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_delete_posts', 1]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_site_config', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_templates', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_styles', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_icon_sets', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_users', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_roles', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_filetypes', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_defaults', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_config_lock_override', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_news', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_file_filters', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_assign_role', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_create', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_delete', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_transfer', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_lock', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_config', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_bans', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_sticky_posts', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_lock_posts', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_post_in_locked', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_post_as_staff', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_mod_comment', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_modify_posts', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_reports', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_mod_mode', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_delete_posts', 0]);
    }
}