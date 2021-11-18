<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableRolePermissions extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_ROLE_PERMISSIONS_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'role_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'permission' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'perm_setting' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'role_id' => ['row_check' => true, 'auto_inc' => false],
            'permission' => ['row_check' => true, 'auto_inc' => false],
            'perm_setting' => ['row_check' => false, 'auto_inc' => false]];
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
            CONSTRAINT fk1_" . $this->table_name . "_" . NEL_ROLES_TABLE . "
            FOREIGN KEY (role_id) REFERENCES " . NEL_ROLES_TABLE . " (role_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk2_" . $this->table_name . "_" . NEL_PERMISSIONS_TABLE . "
            FOREIGN KEY (permission) REFERENCES " . NEL_PERMISSIONS_TABLE . " (permission)
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
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_bans_view', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_bans_add', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_bans_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_bans_delete', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_blotter_manage', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_boards_view', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_boards_add', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_boards_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_boards_delete', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_config_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_config_override', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_board_defaults_modify', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_filetypes_manage', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_file_filters_manage', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_image_sets_manage', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_logs_view', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_logs_manage', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_news_manage', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_noticeboard_view', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_noticeboard_post', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_noticeboard_delete', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_pages_manage', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_permissions_manage', 0]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_reports_view', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_reports_dismiss', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_roles_view', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_roles_manage', 0]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_site_config_modify', 0]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_styles_manage', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_templates_manage', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_users_view', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_users_manage', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_word_filters_manage', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_regen_cache', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_regen_pages', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_regen_overboard', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_post_status', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_post_type', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_edit_posts', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_delete_by_ip', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_post_as_staff', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_post_locked_thread', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_post_locked_board', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_custom_name', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_custom_capcode', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_bypass_renzoku', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_delete_posts', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_view_unhashed_ip', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_private_messages_use', 1]);
        $this->insertDefaultRow(['SITE_ADMIN', 'perm_raw_html', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_bans_view', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_bans_add', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_bans_modify', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_bans_delete', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_blotter_manage', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_boards_view', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_boards_add', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_boards_modify', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_boards_delete', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_config_modify', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_config_override', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_board_defaults_modify', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_filetypes_manage', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_file_filters_manage', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_image_sets_manage', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_logs_view', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_logs_manage', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_news_manage', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_noticeboard_view', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_noticeboard_post', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_noticeboard_delete', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_pages_manage', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_permissions_manage', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_reports_view', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_reports_dismiss', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_roles_view', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_roles_manage', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_site_config_modify', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_styles_manage', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_templates_manage', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_users_view', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_users_manage', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_word_filters_manage', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_regen_cache', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_regen_pages', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_regen_overboard', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_post_status', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_post_type', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_edit_posts', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_delete_by_ip', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_post_as_staff', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_post_locked_thread', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_post_locked_board', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_custom_name', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_custom_capcode', 0]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_bypass_renzoku', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_delete_posts', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_view_unhashed_ip', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_private_messages_use', 1]);
        $this->insertDefaultRow(['BOARD_OWNER', 'perm_raw_html', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_bans_view', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_bans_add', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_bans_modify', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_bans_delete', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_blotter_manage', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_boards_view', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_boards_add', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_boards_modify', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_boards_delete', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_config_modify', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_config_override', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_board_defaults_modify', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_filetypes_manage', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_file_filters_manage', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_image_sets_manage', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_logs_view', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_logs_manage', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_news_manage', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_noticeboard_view', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_noticeboard_post', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_noticeboard_delete', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_pages_manage', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_permissions_manage', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_reports_view', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_reports_dismiss', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_roles_view', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_roles_manage', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_site_config_modify', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_styles_manage', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_templates_manage', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_users_view', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_users_manage', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_word_filters_manage', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_regen_overboard', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_post_status', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_post_type', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_edit_posts', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_delete_by_ip', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_post_as_staff', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_post_locked_thread', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_post_locked_board', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_custom_name', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_custom_capcode', 0]);
        $this->insertDefaultRow(['MODERATOR', 'perm_bypass_renzoku', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_delete_posts', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_view_unhashed_ip', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_private_messages_use', 1]);
        $this->insertDefaultRow(['MODERATOR', 'perm_raw_html', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_bans_view', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_bans_add', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_bans_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_bans_delete', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_blotter_manage', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_boards_view', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_boards_add', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_boards_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_boards_delete', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_config_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_config_override', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_board_defaults_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_filetypes_manage', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_file_filters_manage', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_image_sets_manage', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_logs_view', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_logs_manage', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_news_manage', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_noticeboard_view', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_noticeboard_post', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_noticeboard_delete', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_pages_manage', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_permissions_manage', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_reports_view', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_reports_dismiss', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_roles_view', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_roles_manage', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_site_config_modify', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_styles_manage', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_templates_manage', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_threads_access', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_users_view', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_users_manage', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_word_filters_manage', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_regen_overboard', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_post_status', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_post_type', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_edit_posts', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_delete_by_ip', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_post_as_staff', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_post_locked_thread', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_post_locked_board', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_custom_name', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_custom_capcode', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_bypass_renzoku', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_delete_posts', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_view_unhashed_ip', 0]);
        $this->insertDefaultRow(['JANITOR', 'perm_private_messages_use', 1]);
        $this->insertDefaultRow(['JANITOR', 'perm_raw_html', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_bans_view', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_bans_add', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_bans_modify', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_bans_delete', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_blotter_manage', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_boards_view', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_boards_add', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_boards_modify', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_boards_delete', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_config_modify', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_config_override', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_board_defaults_modify', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_filetypes_manage', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_file_filters_manage', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_image_sets_manage', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_logs_view', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_logs_manage', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_news_manage', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_noticeboard_view', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_noticeboard_post', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_noticeboard_delete', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_pages_manage', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_permissions_manage', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_reports_view', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_reports_dismiss', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_roles_view', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_roles_manage', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_site_config_modify', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_styles_manage', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_templates_manage', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_threads_access', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_users_view', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_users_manage', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_word_filters_manage', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_regen_overboard', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_post_status', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_post_type', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_edit_posts', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_delete_by_ip', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_post_as_staff', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_post_locked_thread', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_post_locked_board', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_custom_name', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_custom_capcode', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_bypass_renzoku', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_delete_posts', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_view_unhashed_ip', 0]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_private_messages_use', 1]);
        $this->insertDefaultRow(['BASIC_USER', 'perm_raw_html', 0]);
    }
}