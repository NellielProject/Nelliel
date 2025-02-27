<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableRolePermissions extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'role_id' => 'string',
        'permission' => 'string',
        'perm_setting' => 'boolean'];

    public const PDO_TYPES = [
        'role_id' => PDO::PARAM_STR,
        'permission' => PDO::PARAM_STR,
        'perm_setting' => PDO::PARAM_INT];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_ROLE_PERMISSIONS_TABLE;
        $this->column_checks = [
            'role_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'permission' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'perm_setting' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
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
        $this->insertDefaultRow(['site_admin', 'perm_view_bans', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_add_bans', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_add_range_bans', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_modify_bans', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_delete_bans', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_blotter', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_boards_view', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_boards_add', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_boards_modify', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_boards_delete', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_modify_board_config', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_override_config_lock', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_modify_board_defaults', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_content_ops', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_embeds', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_filetypes', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_file_filters', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_image_sets', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_view_ip_info', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_add_ip_notes', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_delete_ip_notes', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_view_public_logs', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_view_system_logs', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_markup', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_news', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_noticeboard_view', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_noticeboard_post', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_noticeboard_delete', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_pages', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_plugins', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_access_plugin_controls', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_view_reports', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_dismiss_reports', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_view_roles', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_roles', 0]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_scripts', 0]);
        $this->insertDefaultRow(['site_admin', 'perm_modify_site_config', 0]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_styles', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_templates', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_view_users', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_users', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_wordfilters', 1]);
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
        $this->insertDefaultRow(['site_admin', 'perm_use_private_messages', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_manage_private_messages', 1]);
        $this->insertDefaultRow(['site_admin', 'perm_raw_html', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_view_bans', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_add_bans', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_add_range_bans', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_modify_bans', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_delete_bans', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_blotter', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_boards_view', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_boards_add', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_boards_modify', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_boards_delete', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_modify_board_config', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_override_config_lock', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_modify_board_defaults', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_content_ops', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_embeds', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_filetypes', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_file_filters', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_image_sets', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_view_ip_info', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_add_ip_notes', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_delete_ip_notes', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_view_public_logs', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_view_system_logs', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_markup', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_news', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_noticeboard_view', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_noticeboard_post', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_noticeboard_delete', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_pages', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_plugins', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_access_plugin_controls', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_view_reports', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_dismiss_reports', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_view_roles', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_roles', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_scripts', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_modify_site_config', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_styles', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_templates', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_view_users', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_users', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_wordfilters', 1]);
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
        $this->insertDefaultRow(['board_owner', 'perm_use_private_messages', 1]);
        $this->insertDefaultRow(['board_owner', 'perm_manage_private_messages', 0]);
        $this->insertDefaultRow(['board_owner', 'perm_raw_html', 0]);
        $this->insertDefaultRow(['moderator', 'perm_view_bans', 1]);
        $this->insertDefaultRow(['moderator', 'perm_add_bans', 1]);
        $this->insertDefaultRow(['moderator', 'perm_add_range_bans', 0]);
        $this->insertDefaultRow(['moderator', 'perm_modify_bans', 1]);
        $this->insertDefaultRow(['moderator', 'perm_delete_bans', 1]);
        $this->insertDefaultRow(['moderator', 'perm_manage_blotter', 0]);
        $this->insertDefaultRow(['moderator', 'perm_boards_view', 0]);
        $this->insertDefaultRow(['moderator', 'perm_boards_add', 0]);
        $this->insertDefaultRow(['moderator', 'perm_boards_modify', 0]);
        $this->insertDefaultRow(['moderator', 'perm_boards_delete', 0]);
        $this->insertDefaultRow(['moderator', 'perm_modify_board_config', 0]);
        $this->insertDefaultRow(['moderator', 'perm_override_config_lock', 0]);
        $this->insertDefaultRow(['moderator', 'perm_modify_board_defaults', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_content_ops', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_embeds', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_filetypes', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_file_filters', 1]);
        $this->insertDefaultRow(['moderator', 'perm_manage_image_sets', 0]);
        $this->insertDefaultRow(['moderator', 'perm_view_ip_info', 1]);
        $this->insertDefaultRow(['moderator', 'perm_add_ip_notes', 1]);
        $this->insertDefaultRow(['moderator', 'perm_delete_ip_notes', 0]);
        $this->insertDefaultRow(['moderator', 'perm_view_public_logs', 1]);
        $this->insertDefaultRow(['moderator', 'perm_view_system_logs', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_markup', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_news', 0]);
        $this->insertDefaultRow(['moderator', 'perm_noticeboard_view', 1]);
        $this->insertDefaultRow(['moderator', 'perm_noticeboard_post', 1]);
        $this->insertDefaultRow(['moderator', 'perm_noticeboard_delete', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_pages', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_plugins', 0]);
        $this->insertDefaultRow(['moderator', 'perm_access_plugin_controls', 0]);
        $this->insertDefaultRow(['moderator', 'perm_view_reports', 1]);
        $this->insertDefaultRow(['moderator', 'perm_dismiss_reports', 1]);
        $this->insertDefaultRow(['moderator', 'perm_view_roles', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_roles', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_scripts', 0]);
        $this->insertDefaultRow(['moderator', 'perm_modify_site_config', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_styles', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_templates', 0]);
        $this->insertDefaultRow(['moderator', 'perm_threads_access', 1]);
        $this->insertDefaultRow(['moderator', 'perm_view_users', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_users', 0]);
        $this->insertDefaultRow(['moderator', 'perm_manage_wordfilters', 1]);
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
        $this->insertDefaultRow(['moderator', 'perm_use_private_messages', 1]);
        $this->insertDefaultRow(['moderator', 'perm_manage_private_messages', 0]);
        $this->insertDefaultRow(['moderator', 'perm_raw_html', 0]);
        $this->insertDefaultRow(['janitor', 'perm_view_bans', 0]);
        $this->insertDefaultRow(['janitor', 'perm_add_bans', 0]);
        $this->insertDefaultRow(['janitor', 'perm_add_range_bans', 0]);
        $this->insertDefaultRow(['janitor', 'perm_modify_bans', 0]);
        $this->insertDefaultRow(['janitor', 'perm_delete_bans', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_blotter', 0]);
        $this->insertDefaultRow(['janitor', 'perm_boards_view', 0]);
        $this->insertDefaultRow(['janitor', 'perm_boards_add', 0]);
        $this->insertDefaultRow(['janitor', 'perm_boards_modify', 0]);
        $this->insertDefaultRow(['janitor', 'perm_boards_delete', 0]);
        $this->insertDefaultRow(['janitor', 'perm_modify_board_config', 0]);
        $this->insertDefaultRow(['janitor', 'perm_override_config_lock', 0]);
        $this->insertDefaultRow(['janitor', 'perm_modify_board_defaults', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_content_ops', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_embeds', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_filetypes', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_file_filters', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_image_sets', 0]);
        $this->insertDefaultRow(['janitor', 'perm_view_ip_info', 1]);
        $this->insertDefaultRow(['janitor', 'perm_add_ip_notes', 1]);
        $this->insertDefaultRow(['janitor', 'perm_delete_ip_notes', 0]);
        $this->insertDefaultRow(['janitor', 'perm_view_public_logs', 0]);
        $this->insertDefaultRow(['janitor', 'perm_view_system_logs', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_markup', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_news', 0]);
        $this->insertDefaultRow(['janitor', 'perm_noticeboard_view', 1]);
        $this->insertDefaultRow(['janitor', 'perm_noticeboard_post', 0]);
        $this->insertDefaultRow(['janitor', 'perm_noticeboard_delete', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_pages', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_plugins', 0]);
        $this->insertDefaultRow(['janitor', 'perm_access_plugin_controls', 0]);
        $this->insertDefaultRow(['janitor', 'perm_view_reports', 1]);
        $this->insertDefaultRow(['janitor', 'perm_dismiss_reports', 1]);
        $this->insertDefaultRow(['janitor', 'perm_view_roles', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_roles', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_scripts', 0]);
        $this->insertDefaultRow(['janitor', 'perm_modify_site_config', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_styles', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_templates', 0]);
        $this->insertDefaultRow(['janitor', 'perm_threads_access', 0]);
        $this->insertDefaultRow(['janitor', 'perm_view_users', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_users', 0]);
        $this->insertDefaultRow(['janitor', 'perm_manage_wordfilters', 0]);
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
        $this->insertDefaultRow(['janitor', 'perm_use_private_messages', 1]);
        $this->insertDefaultRow(['janitor', 'perm_manage_private_messages', 0]);
        $this->insertDefaultRow(['janitor', 'perm_raw_html', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_view_bans', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_add_bans', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_add_range_bans', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_modify_bans', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_delete_bans', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_blotter', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_boards_view', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_boards_add', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_boards_modify', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_boards_delete', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_modify_board_config', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_override_config_lock', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_modify_board_defaults', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_content_ops', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_embeds', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_filetypes', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_file_filters', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_image_sets', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_view_ip_info', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_add_ip_notes', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_delete_ip_notes', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_view_public_logs', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_view_system_logs', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_markup', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_news', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_noticeboard_view', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_noticeboard_post', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_noticeboard_delete', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_pages', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_plugins', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_access_plugin_controls', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_view_reports', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_dismiss_reports', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_view_roles', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_roles', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_scripts', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_modify_site_config', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_styles', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_templates', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_threads_access', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_view_users', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_users', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_wordfilters', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_regen_cache', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_regen_pages', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_regen_overboard', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_extract_gettext', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_modify_content_status', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_edit_posts', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_delete_by_ip', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_mod_mode', 0]);
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
        $this->insertDefaultRow(['basic_user', 'perm_use_private_messages', 1]);
        $this->insertDefaultRow(['basic_user', 'perm_manage_private_messages', 0]);
        $this->insertDefaultRow(['basic_user', 'perm_raw_html', 0]);
    }
}