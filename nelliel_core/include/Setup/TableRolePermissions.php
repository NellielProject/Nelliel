<?php

declare(strict_types=1);


namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableRolePermissions extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_ROLE_PERMISSIONS_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'role_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'permission' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'perm_setting' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            role_id         VARCHAR(50) NOT NULL,
            permission      VARCHAR(50) NOT NULL,
            perm_setting    SMALLINT NOT NULL DEFAULT 0,
            CONSTRAINT fk1_" . $this->table_name . "_" . $other_tables['roles_table'] . "
            FOREIGN KEY (role_id) REFERENCES " . $other_tables['roles_table'] . " (role_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk2_" . $this->table_name . "_" . $other_tables['permissions_table'] . "
            FOREIGN KEY (permission) REFERENCES " . $other_tables['permissions_table'] . " (permission)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_bans', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_boards', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_board_config', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_board_config_override', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_board_defaults', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_filetypes', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_file_filters', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_icon_sets', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_ifthens', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_logs', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_news', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_permissions', 0]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_reports', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_roles', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_site_config', 0]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_styles', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_templates', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_manage_users', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_delete_posts', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_post_status', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_mod_comment', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_mod_mode', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_post_as_staff', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_post_in_locked', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_transfer', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_extract_gettext', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_regen_cache', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_regen_pages', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_view_unhashed_ip', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_use_pms', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_staff_board_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_staff_board_post', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_staff_board_delete', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_staff_custom_capcode', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_bans', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_boards', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_board_config', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_board_config_override', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_board_defaults', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_filetypes', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_file_filters', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_icon_sets', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_ifthens', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_logs', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_news', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_permissions', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_reports', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_roles', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_site_config', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_styles', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_templates', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_manage_users', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_delete_posts', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_post_status', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_mod_comment', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_mod_mode', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_post_as_staff', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_post_in_locked', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_transfer', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_regen_cache', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_regen_pages', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_view_unhashed_ip', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_use_pms', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_staff_board_access', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_staff_board_post', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_staff_board_delete', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_staff_custom_capcode', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_bans', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_boards', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_board_config', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_board_config_override', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_board_defaults', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_filetypes', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_file_filters', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_icon_sets', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_ifthens', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_logs', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_news', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_permissions', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_reports', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_roles', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_site_config', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_styles', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_templates', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_manage_users', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_delete_posts', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_post_status', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_mod_comment', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_mod_mode', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_post_as_staff', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_post_in_locked', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_transfer', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_view_unhashed_ip', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_use_pms', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_staff_board_access', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_staff_board_post', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_staff_board_delete', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_staff_custom_capcode', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_bans', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_boards', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_board_config', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_board_config_override', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_board_defaults', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_filetypes', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_file_filters', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_icon_sets', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_ifthens', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_logs', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_news', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_permissions', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_reports', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_roles', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_site_config', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_styles', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_templates', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_manage_users', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_delete_posts', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_post_status', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_mod_comment', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_mod_mode', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_post_as_staff', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_post_in_locked', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_transfer', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_view_unhashed_ip', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_use_pms', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_staff_board_access', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_staff_board_post', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_staff_board_delete', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_staff_custom_capcode', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_bans', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_boards', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_board_config', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_board_config_override', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_board_defaults', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_filetypes', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_file_filters', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_icon_sets', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_ifthens', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_logs', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_news', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_permissions', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_reports', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_roles', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_site_config', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_styles', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_templates', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_manage_users', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_delete_posts', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_post_status', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_mod_comment', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_mod_mode', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_post_as_staff', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_post_in_locked', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_transfer', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_view_unhashed_ip', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_use_pms', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_staff_board_access', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_staff_board_post', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_staff_board_delete', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_staff_custom_capcode', 0]);
    }
}