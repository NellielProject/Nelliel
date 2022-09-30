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
            'role_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'permission' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'perm_setting' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT]];
        $this->column_checks = [
            'role_id' => ['row_check' => true, 'auto_inc' => false],
            'permission' => ['row_check' => true, 'auto_inc' => false],
            'perm_setting' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            role_id         VARCHAR(50) NOT NULL,
            permission      VARCHAR(50) NOT NULL,
            perm_setting    SMALLINT NOT NULL DEFAULT 0,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (role_id, permission),
            CONSTRAINT fk_role_permissions__roles
            FOREIGN KEY (role_id) REFERENCES ' . NEL_ROLES_TABLE . ' (role_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk_role_permissions__permissions
            FOREIGN KEY (permission) REFERENCES ' . NEL_PERMISSIONS_TABLE . ' (permission)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['site_admin', 'perm_bans_view', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_bans_add', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_bans_modify', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_bans_delete', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_blotter_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_boards_view', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_boards_add', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_boards_modify', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_boards_delete', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_board_config_modify', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_board_config_override', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_board_defaults_modify', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_content_ops_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_embeds_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_filetypes_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_file_filters_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_image_sets_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_ip_notes_view', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_ip_notes_add', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_ip_notes_delete', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_logs_view', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_logs_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_news_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_noticeboard_view', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_noticeboard_post', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_noticeboard_delete', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_pages_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_permissions_manage', 0]);
        $this->insertDefaultRow(['site_admin', 'perm_plugins_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_reports_view', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_reports_dismiss', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_roles_view', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_roles_manage', 0]);
        $this->insertDefaultRow(['site_admin', 'perm_site_config_modify', 0]);
        $this->insertDefaultRow(['site_admin', 'perm_styles_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_templates_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_users_view', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_users_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_wordfilters_manage', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_regen_cache', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_regen_pages', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_regen_overboard', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['site_admin', 'perm_modify_content_status', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_edit_posts', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_delete_by_ip', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_post_as_staff', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_post_locked_thread', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_post_locked_board', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_move_content', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_merge_threads', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_search_posts', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_custom_name', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_custom_capcode', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_bypass_renzoku', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_delete_content', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_view_unhashed_ip', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_private_messages_use', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_raw_html', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_bans_view', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_bans_add', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_bans_modify', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_bans_delete', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_blotter_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_boards_view', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_boards_add', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_boards_modify', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_boards_delete', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_board_config_modify', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_board_config_override', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_board_defaults_modify', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_content_ops_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_embeds_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_filetypes_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_file_filters_manage', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_image_sets_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_ip_notes_view', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_ip_notes_add', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_ip_notes_delete', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_logs_view', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_logs_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_news_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_noticeboard_view', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_noticeboard_post', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_noticeboard_delete', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_pages_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_permissions_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_plugins_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_reports_view', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_reports_dismiss', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_roles_view', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_roles_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_site_config_modify', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_styles_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_templates_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_users_view', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_users_manage', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_wordfilters_manage', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_regen_cache', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_regen_pages', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_regen_overboard', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_modify_content_status', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_edit_posts', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_delete_by_ip', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_post_as_staff', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_post_locked_thread', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_post_locked_board', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_move_content', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_merge_threads', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_search_posts', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_custom_name', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_custom_capcode', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_bypass_renzoku', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_delete_content', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_view_unhashed_ip', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_private_messages_use', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_raw_html', 0]);
        $this->insertDefaultRow(['moderator', 'perm_bans_view', 1]);
        $this->insertDefaultRow(['moderator', 'perm_bans_add', 1]);
        $this->insertDefaultRow(['moderator', 'perm_bans_modify', 1]);
        $this->insertDefaultRow(['moderator', 'perm_bans_delete', 1]);
        $this->insertDefaultRow(['moderator', 'perm_blotter_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_boards_view', 0]);
        $this->insertDefaultRow(['moderator', 'perm_boards_add', 0]);
        $this->insertDefaultRow(['moderator', 'perm_boards_modify', 0]);
        $this->insertDefaultRow(['moderator', 'perm_boards_delete', 0]);
        $this->insertDefaultRow(['moderator', 'perm_board_config_modify', 0]);
        $this->insertDefaultRow(['moderator', 'perm_board_config_override', 0]);
        $this->insertDefaultRow(['moderator', 'perm_board_defaults_modify', 0]);
        $this->insertDefaultRow(['moderator', 'perm_content_ops_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_embeds_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_filetypes_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_file_filters_manage', 1]);
        $this->insertDefaultRow(['moderator', 'perm_image_sets_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_ip_notes_view', 1]);
        $this->insertDefaultRow(['moderator', 'perm_ip_notes_add', 1]);
        $this->insertDefaultRow(['moderator', 'perm_ip_notes_delete', 0]);
        $this->insertDefaultRow(['moderator', 'perm_logs_view', 1]);
        $this->insertDefaultRow(['moderator', 'perm_logs_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_news_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_noticeboard_view', 1]);
        $this->insertDefaultRow(['moderator', 'perm_noticeboard_post', 1]);
        $this->insertDefaultRow(['moderator', 'perm_noticeboard_delete', 0]);
        $this->insertDefaultRow(['moderator', 'perm_pages_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_permissions_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_plugins_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_reports_view', 1]);
        $this->insertDefaultRow(['moderator', 'perm_reports_dismiss', 1]);
        $this->insertDefaultRow(['moderator', 'perm_roles_view', 0]);
        $this->insertDefaultRow(['moderator', 'perm_roles_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_site_config_modify', 0]);
        $this->insertDefaultRow(['moderator', 'perm_styles_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_templates_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['moderator', 'perm_users_view', 0]);
        $this->insertDefaultRow(['moderator', 'perm_users_manage', 0]);
        $this->insertDefaultRow(['moderator', 'perm_wordfilters_manage', 1]);
        $this->insertDefaultRow(['moderator', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['moderator', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['moderator', 'perm_regen_overboard', 0]);
        $this->insertDefaultRow(['moderator', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['moderator', 'perm_modify_content_status', 1]);
        $this->insertDefaultRow(['moderator', 'perm_edit_posts', 0]);
        $this->insertDefaultRow(['moderator', 'perm_delete_by_ip', 1]);
        $this->insertDefaultRow(['moderator', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['moderator', 'perm_post_as_staff', 1]);
        $this->insertDefaultRow(['moderator', 'perm_post_locked_thread', 1]);
        $this->insertDefaultRow(['moderator', 'perm_post_locked_board', 0]);
        $this->insertDefaultRow(['moderator', 'perm_move_content', 0]);
        $this->insertDefaultRow(['moderator', 'perm_merge_threads', 1]);
        $this->insertDefaultRow(['moderator', 'perm_search_posts', 1]);
        $this->insertDefaultRow(['moderator', 'perm_custom_name', 0]);
        $this->insertDefaultRow(['moderator', 'perm_custom_capcode', 0]);
        $this->insertDefaultRow(['moderator', 'perm_bypass_renzoku', 1]);
        $this->insertDefaultRow(['moderator', 'perm_delete_content', 1]);
        $this->insertDefaultRow(['moderator', 'perm_view_unhashed_ip', 1]);
        $this->insertDefaultRow(['moderator', 'perm_private_messages_use', 1]);
        $this->insertDefaultRow(['moderator', 'perm_raw_html', 0]);
        $this->insertDefaultRow(['janitor', 'perm_bans_view', 0]);
        $this->insertDefaultRow(['janitor', 'perm_bans_add', 0]);
        $this->insertDefaultRow(['janitor', 'perm_bans_modify', 0]);
        $this->insertDefaultRow(['janitor', 'perm_bans_delete', 0]);
        $this->insertDefaultRow(['janitor', 'perm_blotter_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_boards_view', 0]);
        $this->insertDefaultRow(['janitor', 'perm_boards_add', 0]);
        $this->insertDefaultRow(['janitor', 'perm_boards_modify', 0]);
        $this->insertDefaultRow(['janitor', 'perm_boards_delete', 0]);
        $this->insertDefaultRow(['janitor', 'perm_board_config_modify', 0]);
        $this->insertDefaultRow(['janitor', 'perm_board_config_override', 0]);
        $this->insertDefaultRow(['janitor', 'perm_board_defaults_modify', 0]);
        $this->insertDefaultRow(['janitor', 'perm_content_ops_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_embeds_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_filetypes_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_file_filters_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_image_sets_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_ip_notes_view', 1]);
        $this->insertDefaultRow(['janitor', 'perm_ip_notes_add', 1]);
        $this->insertDefaultRow(['janitor', 'perm_ip_notes_delete', 0]);
        $this->insertDefaultRow(['janitor', 'perm_logs_view', 0]);
        $this->insertDefaultRow(['janitor', 'perm_logs_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_news_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_noticeboard_view', 1]);
        $this->insertDefaultRow(['janitor', 'perm_noticeboard_post', 0]);
        $this->insertDefaultRow(['janitor', 'perm_noticeboard_delete', 0]);
        $this->insertDefaultRow(['janitor', 'perm_pages_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_permissions_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_plugins_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_reports_view', 1]);
        $this->insertDefaultRow(['janitor', 'perm_reports_dismiss', 1]);
        $this->insertDefaultRow(['janitor', 'perm_roles_view', 0]);
        $this->insertDefaultRow(['janitor', 'perm_roles_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_site_config_modify', 0]);
        $this->insertDefaultRow(['janitor', 'perm_styles_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_templates_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_threads_access', 0]);
        $this->insertDefaultRow(['janitor', 'perm_users_view', 0]);
        $this->insertDefaultRow(['janitor', 'perm_users_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_wordfilters_manage', 0]);
        $this->insertDefaultRow(['janitor', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['janitor', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['janitor', 'perm_regen_overboard', 0]);
        $this->insertDefaultRow(['janitor', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['janitor', 'perm_modify_content_status', 0]);
        $this->insertDefaultRow(['janitor', 'perm_edit_posts', 0]);
        $this->insertDefaultRow(['janitor', 'perm_delete_by_ip', 0]);
        $this->insertDefaultRow(['janitor', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['janitor', 'perm_post_as_staff', 0]);
        $this->insertDefaultRow(['janitor', 'perm_post_locked_thread', 0]);
        $this->insertDefaultRow(['janitor', 'perm_post_locked_board', 0]);
        $this->insertDefaultRow(['janitor', 'perm_move_content', 0]);
        $this->insertDefaultRow(['janitor', 'perm_merge_threads', 0]);
        $this->insertDefaultRow(['janitor', 'perm_search_posts', 1]);
        $this->insertDefaultRow(['janitor', 'perm_custom_name', 0]);
        $this->insertDefaultRow(['janitor', 'perm_custom_capcode', 0]);
        $this->insertDefaultRow(['janitor', 'perm_bypass_renzoku', 0]);
        $this->insertDefaultRow(['janitor', 'perm_delete_content', 1]);
        $this->insertDefaultRow(['janitor', 'perm_view_unhashed_ip', 0]);
        $this->insertDefaultRow(['janitor', 'perm_private_messages_use', 1]);
        $this->insertDefaultRow(['janitor', 'perm_raw_html', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_bans_view', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_bans_add', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_bans_modify', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_bans_delete', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_blotter_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_boards_view', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_boards_add', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_boards_modify', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_boards_delete', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_board_config_modify', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_board_config_override', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_board_defaults_modify', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_content_ops_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_embeds_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_filetypes_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_file_filters_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_image_sets_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_ip_notes_view', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_ip_notes_add', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_ip_notes_delete', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_logs_view', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_logs_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_news_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_noticeboard_view', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_noticeboard_post', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_noticeboard_delete', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_pages_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_permissions_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_plugins_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_reports_view', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_reports_dismiss', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_roles_view', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_roles_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_site_config_modify', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_styles_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_templates_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_threads_access', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_users_view', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_users_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_wordfilters_manage', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_regen_overboard', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_modify_content_status', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_edit_posts', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_delete_by_ip', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_mod_mode', 1]);
        $this->insertDefaultRow(['basic_user', 'perm_post_as_staff', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_post_locked_thread', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_post_locked_board', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_move_content', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_merge_threads', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_search_posts', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_custom_name', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_custom_capcode', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_bypass_renzoku', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_delete_content', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_view_unhashed_ip', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_private_messages_use', 1]);
        $this->insertDefaultRow(['basic_user', 'perm_raw_html', 0]);
    }
}