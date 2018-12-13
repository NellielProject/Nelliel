<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class TableInsertData
{

    function __construct()
    {
    }

    public function siteConfigDefaults()
    {
        $database = nel_database();
        $insert_query = "INSERT INTO " . SITE_CONFIG_TABLE . " (config_type, config_owner, config_category, data_type, config_name, setting) VALUES (?, ?, ?, ?, ?, ?)";
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'general', 'str', 'home_page', '../']);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'crypt', 'str', 'post_password_algorithm', 'sha256']);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'crypt', 'str', 'secure_tripcode_algorithm', 'sha256']);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'crypt', 'bool', 'do_password_rehash', '0']);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'output', 'str', 'index_filename_format', 'index-%d']);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'output', 'str', 'thread_filename_format', 'thread-%d']);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'general', 'bool', 'template_id', 'nelliel-template-basic']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_bans_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_bans_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_board_data_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_board_data_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_board_defaults_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_board_defaults_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_filetypes_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_filetypes_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_file_filters_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_file_filters_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_login_attempts_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_login_attempts_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_permissions_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_permissions_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_reports_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_reports_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_roles_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_roles_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_site_config_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_site_config_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_users_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_users_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_user_role_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_user_role_schema', '001']);

        nel_setup_stuff_done(true);
    }

    public function roleDefaults()
    {
        $database = nel_database();
        $insert_query = "INSERT INTO " . ROLES_TABLE . " (role_id, role_level, role_title, capcode_text) VALUES (?, ?, ?, ?)";
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 1000, 'Site Administrator', '## Site Administrator ##']);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 100, 'Board Administrator', '## Board Administrator ##']);
        $database->executePrepared($prepared, ['MOD', 50, 'Moderator', '## Moderator ##']);
        $database->executePrepared($prepared, ['JANITOR', 10, 'Janitor', '## Janitor ##']);
        nel_setup_stuff_done(true);
    }

    public function permissionsDefaults()
    {
        $database = nel_database();
        $insert_query = 'INSERT INTO "' . PERMISSIONS_TABLE . '" ("role_id", "perm_id", "perm_setting") VALUES (?, ?, ?)';
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_site_config_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_site_config_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_board_defaults_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_board_defaults_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_board_config_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_board_config_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_user_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_user_add', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_user_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_user_delete', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_role_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_role_add', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_role_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_role_delete', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_ban_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_ban_add', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_ban_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_ban_delete', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_modmode_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_threads_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_threads_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_post_delete', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_post_as_staff', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_post_in_locked', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_post_sticky', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_post_lock', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_post_mod_comment', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_reports_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_reports_dismiss', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_regen_cache', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_regen_pages', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_manage_boards_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_manage_boards_add', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_manage_boards_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_manage_boards_delete', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_extract_gettext', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_file_filters_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_file_filters_add', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_file_filters_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_file_filters_delete', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_templates_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_templates_add', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_templates_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_templates_delete', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_filetypes_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_filetypes_add', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_filetypes_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_filetypes_delete', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_styles_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_styles_add', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_styles_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_styles_delete', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_site_config_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_site_config_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_board_defaults_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_board_defaults_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_board_config_access', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_board_config_modify', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_user_access', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_user_add', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_user_modify', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_user_delete', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_role_access', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_role_add', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_role_modify', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_role_delete', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_ban_access', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_ban_add', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_ban_modify', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_ban_delete', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_modmode_access', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_threads_access', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_threads_modify', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_post_delete', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_post_as_staff', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_post_in_locked', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_post_sticky', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_post_lock', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_post_mod_comment', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_reports_access', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_reports_dismiss', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_regen_cache', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_regen_pages', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_manage_boards_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_manage_boards_add', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_manage_boards_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_manage_boards_delete', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_extract_gettext', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_file_filters_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_file_filters_add', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_file_filters_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_file_filters_delete', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_templates_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_templates_add', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_templates_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_templates_delete', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_filetypes_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_filetypes_add', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_filetypes_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_filetypes_delete', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_styles_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_styles_add', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_styles_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_styles_delete', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_site_config_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_site_config_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_board_defaults_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_board_defaults_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_board_config_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_board_config_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_user_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_user_add', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_user_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_user_delete', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_role_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_role_add', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_role_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_role_delete', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_ban_access', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_ban_add', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_ban_modify', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_ban_delete', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_modmode_access', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_threads_access', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_threads_modify', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_post_delete', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_post_as_staff', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_post_in_locked', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_post_sticky', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_post_lock', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_post_mod_comment', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_reports_access', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_reports_dismiss', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_regen_cache', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_regen_pages', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_manage_boards_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_manage_boards_add', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_manage_boards_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_manage_boards_delete', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_extract_gettext', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_file_filters_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_file_filters_add', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_file_filters_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_file_filters_delete', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_templates_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_templates_add', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_templates_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_templates_delete', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_filetypes_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_filetypes_add', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_filetypes_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_filetypes_delete', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_styles_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_styles_add', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_styles_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_styles_delete', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_site_config_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_site_config_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_board_defaults_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_board_defaults_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_board_config_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_board_config_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_user_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_user_add', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_user_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_user_delete', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_role_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_role_add', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_role_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_role_delete', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_ban_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_ban_add', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_ban_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_ban_delete', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_modmode_access', 1]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_threads_access', 1]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_threads_modify', 1]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_post_delete', 1]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_post_as_staff', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_post_in_locked', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_post_sticky', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_post_lock', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_post_mod_comment', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_reports_access', 1]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_reports_dismiss', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_regen_cache', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_regen_pages', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_manage_boards_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_manage_boards_add', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_manage_boards_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_manage_boards_delete', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_extract_gettext', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_file_filters_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_file_filters_add', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_file_filters_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_file_filters_delete', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_templates_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_templates_add', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_templates_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_templates_delete', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_filetypes_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_filetypes_add', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_filetypes_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_filetypes_delete', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_styles_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_styles_add', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_styles_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_styles_delete', 0]);
        nel_setup_stuff_done(true);
    }

    public function defaultAdmin()
    {
        if (DEFAULTADMIN === '' || DEFAULTADMIN_PASS === '')
        {
            return false;
        }

        $database = nel_database();
        $result = $database->query('SELECT 1 FROM "' . USER_TABLE . '" WHERE "user_id" = \'' . DEFAULTADMIN . '\'');

        if ($result->fetch() !== false)
        {
            return false;
        }

        $insert_query = "INSERT INTO " . USER_TABLE .
        " (user_id, user_password, active, failed_logins, last_failed_login) VALUES (?, ?, ?, ?, ?)";
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, [DEFAULTADMIN, nel_password_hash(DEFAULTADMIN_PASS, NEL_PASSWORD_ALGORITHM), 1, 1, 0]);
        nel_setup_stuff_done($result);
    }

    public function defaultAdminRole()
    {
        if (DEFAULTADMIN === '' || DEFAULTADMIN_PASS === '')
        {
            return false;
        }

        $database = nel_database();
        $result = $database->query('SELECT 1 FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = \'' . DEFAULTADMIN . '\'');

        if ($result->fetch() !== false)
        {
            return false;
        }

        $insert_query = "INSERT INTO " . USER_ROLE_TABLE . " (user_id, role_id, board) VALUES (?, ?, ?)";
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, [DEFAULTADMIN, 'SUPER_ADMIN', '']);
        nel_setup_stuff_done($result);
    }

    public function copyBoardDefaults($config_table)
    {
        $database = nel_database();
        $insert_query = 'INSERT INTO "' . $config_table . '" SELECT * FROM "' . BOARD_DEFAULTS_TABLE . '"';
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared);
    }

    public function boardConfigDefaults($config_table)
    {
        $database = nel_database();
        $insert_query = "INSERT INTO " . $config_table .
        " (config_type, config_owner, config_category, data_type, config_name, setting) VALUES (?, ?, ?, ?, ?, ?)";
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_config_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_config_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_files_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_files_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_posts_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_posts_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'original_threads_schema', '001']);
        $database->executePrepared($prepared, ['schema_version', 'nelliel', 'database', 'str', 'current_threads_schema', '001']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'allow_tripkeys', '1']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'force_anonymous', '0']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'show_title', '1']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'show_favicon', '0']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'show_logo', '0']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'use_thumb', '1']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'use_magick', '0']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'use_file_icon', '1']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'use_png_thumb', '0']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'animated_gif_preview', '0']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'require_image_start', '1']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'require_image_always', '0']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'allow_multifile', '0']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'allow_op_multifile', '0']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'use_fgsfds', '1']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'use_spambot_trap', '1']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'only_thread_duplicates', '1']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'only_op_duplicates', '1']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'str', 'board_name', 'Nelliel-powered image board']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'str', 'board_favicon', '']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'str', 'board_logo', '']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'board_language', 'en-US']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'thread_delay', '120']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'reply_delay', '60']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'abbreviate_thread', '5']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_post_files', '3']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_files_row', '3']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_multi_width', '175']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_multi_height', '175']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'jpeg_quality', '90']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_width', '256']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_height', '256']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_filesize', '4096']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_name_length', '100']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_email_length', '100']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_subject_length', '100']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_comment_length', '5000']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_comment_lines', '60']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'comment_display_lines', '15']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_source_length', '255']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_license_length', '255']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'threads_per_page', '10']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'page_limit', '10']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'page_buffer', '0']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_posts', '1000']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'int', 'max_bumps', '1000']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'str', 'tripkey_marker', '!']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'str', 'date_format', 'ISO']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'str', 'old_threads', 'ARCHIVE']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'str', 'date_separator', '/']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'str', 'fgsfds_name', 'FGSFDS']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'str', 'indent_marker', '>>']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'file_sha256', '1']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'file_sha512', '0']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'enable_dynamic_pages', '0']);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'bool', 'template_id', 'nelliel-template']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'graphics', '1']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'jpeg', '1']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'gif', '1']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'png', '1']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'jpeg2000', '1']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'tiff', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'bmp', '1']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'icon', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'photoshop', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'tga', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'pict', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'art', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'cel', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'kcf', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'ani', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'icns', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'bool', 'illustrator', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'bool', 'audio', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'bool', 'wave', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'bool', 'aiff', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'bool', 'mp3', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'bool', 'm4a', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'bool', 'flac', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'bool', 'aac', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'bool', 'ogg-audio', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'bool', 'au', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'bool', 'wma', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'bool', 'midi', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'bool', 'ac3', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'bool', 'video', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'bool', 'mpeg', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'bool', 'quicktime', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'bool', 'avi', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'bool', 'wmv', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'bool', 'mpeg4', '1']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'bool', 'matroska', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'bool', 'flash-video', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'bool', 'webm', '1']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'bool', '3gp', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'bool', 'ogg-video', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'bool', 'm4v', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'bool', 'document', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'bool', 'rich-text', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'bool', 'pdf', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'bool', 'msword', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'bool', 'powerpoint', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'bool', 'msexcel', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'bool', 'plaintext', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'bool', 'archive', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'bool', 'gzip', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'bool', 'bzip2', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'bool', 'binhex', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'bool', 'lzh', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'bool', 'zip', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'bool', 'rar', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'bool', 'stuffit', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'bool', 'tar', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'bool', '7z', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'bool', 'iso', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'bool', 'dmg', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'other', 'bool', 'other', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'other', 'bool', 'flash-shockwave', '0']);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'other', 'bool', 'blorb', '0']);
        nel_setup_stuff_done(true);
    }

    public function filetypes()
    {
        $database = nel_database();
        $insert_query = "INSERT INTO " . FILETYPE_TABLE .
        " (extension, parent_extension, type, format, mime, id_regex, label) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, ['', null, 'graphics', null, null, null, 'Graphics files']);
        $database->executePrepared($prepared, ['jpg', 'jpg', 'graphics', 'jpeg', 'image/jpeg', '^\xFF\xD8\xFF', 'JPEG']);
        $database->executePrepared($prepared, ['jpeg', 'jpg', null, null, null, null, null]);
        $database->executePrepared($prepared, ['jpe', 'jpg', null, null, null, null, null]);
        $database->executePrepared($prepared, ['gif', 'gif', 'graphics', 'gif', 'image/gif', '^(?:GIF87a|GIF89a)', 'GIF']);
        $database->executePrepared($prepared, ['png', 'png', 'graphics', 'png', 'image/png', '^\x89\x50\x4E\x47\x0D\x0A\x1A\x0A', 'PNG']);
        $database->executePrepared($prepared, ['jp2', 'jp2', 'graphics', 'jpeg2000', 'image/jp2', '^\x00\x00\x00\x0C\x6A\x50\x2\\x20\x0D\x0A', 'JPEG2000']);
        $database->executePrepared($prepared, ['j2k', 'jp2', null, null, null, null, null]);
        $database->executePrepared($prepared, ['tiff', 'tiff', 'graphics', 'tiff', 'image/tiff', '^I\x20?I\x2A\x00|^MM\x00[\x2A-\x2B]', 'TIFF']);
        $database->executePrepared($prepared, ['tif', 'tiff', null, null, null, null, null]);
        $database->executePrepared($prepared, ['bmp', 'bmp', 'graphics', 'bmp', 'image/x-bmp', '^BM', 'BMP']);
        $database->executePrepared($prepared, ['ico', 'ico', 'graphics', 'icon', 'image/x-icon', '^\x00\x00\x01\x00', 'Icon']);
        $database->executePrepared($prepared, ['psd', 'psd', 'graphics', 'photoshop', 'image/vnd.adobe.photoshop', '^8BPS\x00\x01', 'PSD (Photoshop)']);
        $database->executePrepared($prepared, ['tga', 'tga', 'graphics', 'tga', 'image/x-targa', '^.{1}\x00', 'Truevision TGA']);
        $database->executePrepared($prepared, ['pict', 'pict', 'graphics', 'pict', 'image/x-pict', '^.{522}(?:\x11\x01|\x00\x11\x02\xFF\x0C\x00)', 'PICT']);
        $database->executePrepared($prepared, ['art', 'art', 'graphics', 'art', 'image/x-jg', '^JG[\x03-\x04]\x0E', 'AOL ART']);
        $database->executePrepared($prepared, ['cel', 'cel', 'graphics', 'cel', 'application/octet-stream', '^KiSS(?:\x20\x04|\x20\x08|\x21\x20|\x20\x20)', 'Kisekae CEL']);
        $database->executePrepared($prepared, ['kcf', 'kcf', 'graphics', 'kcf', 'application/octet-stream', '^KiSS\x10)', 'Kisekae Pallete']);
        $database->executePrepared($prepared, ['ani', 'ani', 'graphics', 'ani', 'application/x-navi-animation', '^RIFF\xF2\x19\x00\x00ACONLIST', 'Windows Animated Cursor']);
        $database->executePrepared($prepared, ['icns', 'icns', 'graphics', 'icns', 'image/icns', '^icns', 'Mac OS Icon']);
        $database->executePrepared($prepared, ['ai', 'ai', 'graphics', 'illustrator', 'application/postscript', '^%PDF', 'Adobe Illustrator']);
        $database->executePrepared($prepared, ['ps', 'ps', 'graphics', 'postscript', 'application/postscript', '%!PS', 'PostScript']);
        $database->executePrepared($prepared, ['eps', 'eps', 'graphics', 'postscript', 'application/postscript', '^\xC5\xD0\xD3\xC6|%!PS-Adobe-[0-9]\.[0-9] EPSF-[0-9]\.[0-9]', 'Encapsulated PostScript']);
        $database->executePrepared($prepared, ['', null, 'audio', null, null, null, 'Audio files']);
        $database->executePrepared($prepared, ['wav', 'wav', 'audio', 'wave', 'audio/x-wave', '^RIFF.{4}WAVEfmt', 'WAVE']);
        $database->executePrepared($prepared, ['aif', 'aif', 'audio', 'aiff', 'audio/aiff', '^FORM.{4}AIFF', 'AIFF']);
        $database->executePrepared($prepared, ['aiff', 'aif', null, null, null, null, null]);
        $database->executePrepared($prepared, ['mp3', 'mp3', 'audio', 'mp3', 'audio/mpeg', '^ID3|\xFF[\xE0-\xFF]{1}', 'MP3']);
        $database->executePrepared($prepared, ['m4a', 'm4a', 'audio', 'm4a', 'audio/m4a', '^.{4}ftypM4A', 'MPEG-4 Audio']);
        $database->executePrepared($prepared, ['flac', 'flac', 'audio', 'flac', 'audio/x-flac', '^fLaC\x00\x00\x00\x22', 'FLAC']);
        $database->executePrepared($prepared, ['aac', 'aac', 'audio', 'aac', 'audio/aac', '^ADIF|^\xFF(?:\xF1|\xF9)', 'AAC']);
        $database->executePrepared($prepared, ['ogg', 'ogg', 'audio', 'ogg-audio', 'audio/ogg', '^OggS', 'OGG Audio']);
        $database->executePrepared($prepared, ['au', 'au', 'audio', 'au', 'audio/basic', '^\.snd', 'AU']);
        $database->executePrepared($prepared, ['snd', 'au', null, null, null, null, null]);
        $database->executePrepared($prepared, ['ac3', 'ac3', 'audio', 'ac3', 'audio/ac3', '^\x0B\x77', 'AC3']);
        $database->executePrepared($prepared, ['wma', 'wma', 'audio', 'wma', 'audio/x-ms-wma', '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C', 'Windows Media Audio']);
        $database->executePrepared($prepared, ['midi', 'midi', 'audio', 'midi', 'audio/midi', '^MThd', 'MIDI']);
        $database->executePrepared($prepared, ['mid', 'midi', null, null, null, null, null]);
        $database->executePrepared($prepared, ['', null, 'video', null, null, null, 'Video files']);
        $database->executePrepared($prepared, ['mpg', 'mpg', 'video', 'mpeg', 'video/mpeg', '^\x00\x00\x01[\xB0-\xBF]', 'MPEG-1/MPEG-2']);
        $database->executePrepared($prepared, ['mpeg', 'mpg', null, null, null, null, null]);
        $database->executePrepared($prepared, ['mpe', 'mpg', null, null, null, null, null]);
        $database->executePrepared($prepared, ['mov', 'mov', 'video', 'quicktime', 'video/quicktime', '^.{4}(?:cmov|free|ftypqt|mdat|moov|pnot|skip|wide)', 'Quicktime Movie']);
        $database->executePrepared($prepared, ['avi', 'avi', 'video', 'avi', 'video/x-msvideo', '^RIFF.{4}AVI\sx20LIST', 'AVI']);
        $database->executePrepared($prepared, ['wmv', 'wmv', 'video', 'wmv', 'video/x-ms-wmv', '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C', 'Windows Media Video']);
        $database->executePrepared($prepared, ['mp4', 'mp4', 'video', 'mpeg4', 'video/mp4', '^.{4}ftyp(?:iso2|isom|mp41|mp42)', 'MPEG-4 Media']);
        $database->executePrepared($prepared, ['m4v', 'm4v', 'video', 'm4v', 'video/x-m4v', '^.{4}ftypmp(?:41|42|71)', 'MPEG-4 Video']);
        $database->executePrepared($prepared, ['m4v', 'm4v', 'video', 'm4v', 'video/x-m4v', '^.{4}ftypmp(?:41|42|71)', 'MPEG-4 Video']);
        $database->executePrepared($prepared, ['mkv', 'mkv', 'video', 'matroska', 'video/x-matroska', '^\x1A\x45\xDF\xA3', 'Matroska Media']);
        $database->executePrepared($prepared, ['flv', 'flv', 'video', 'flash-video', 'video/x-flv', '^FLV\x01', 'Flash Video']);
        $database->executePrepared($prepared, ['webm', 'webm', 'video', 'webm', 'video/webm', '^\x1A\x45\xDF\xA3', 'WebM']);
        $database->executePrepared($prepared, ['3gp', '3gp', 'video', '3gp', 'video/3gpp', '^.{4}ftyp3gp', '3GP']);
        $database->executePrepared($prepared, ['ogv', 'ogv', 'video', 'ogg-video', 'video/ogg', '^OggS', 'Ogg Video']);
        $database->executePrepared($prepared, ['', null, 'document', null, null, null, 'Text and document files']);
        $database->executePrepared($prepared, ['rtf', 'rtf', 'document', 'rich-text', 'application/rtf', '^\x7B\x5C\x72\x74\x66\x31', 'Rich Text']);
        $database->executePrepared($prepared, ['pdf', 'pdf', 'document', 'pdf', 'application/pdf', '^\x25PDF', 'PDF']);
        $database->executePrepared($prepared, ['doc', 'doc', 'document', 'msword', 'application/msword', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1|^\xDB\xA5\x2D\x00|^PK\x03\x04', 'Microsoft Word']);
        $database->executePrepared($prepared, ['docx', 'doc', null, null, null, null, null]);
        $database->executePrepared($prepared, ['ppt', 'ppt', 'document', 'powerpoint', 'application/ms-powerpoint', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1|^PK\x03\x04', 'PowerPoint']);
        $database->executePrepared($prepared, ['pptx', 'ppt', null, null, null, null, null]);
        $database->executePrepared($prepared, ['xls', 'xls', 'document', 'msexcel', 'application/ms-excel', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1|^PK\x03\x04', 'Microsoft Excel']);
        $database->executePrepared($prepared, ['xlsx', 'xls', null, null, null, null, null]);
        $database->executePrepared($prepared, ['txt', 'txt', 'document', 'plaintext', 'text/plain', '', 'Plaintext']);
        $database->executePrepared($prepared, ['', null, 'archive', null, null, null, 'Archive files']);
        $database->executePrepared($prepared, ['gz', 'gz', 'archive', 'gzip', 'application/gzip', '^\x1F\x8B\x08', 'GZip']);
        $database->executePrepared($prepared, ['tgz', 'gz', null, null, null, null, null]);
        $database->executePrepared($prepared, ['gzip', 'gz', null, null, null, null, null]);
        $database->executePrepared($prepared, ['bz2', 'bz2', 'archive', 'bzip2', 'application/x-bzip2', '^BZh.{1}\x31\x41\x59\x26\x53\x59', 'bzip2']);
        $database->executePrepared($prepared, ['tbz2', 'bz2', null, null, null, null, null]);
        $database->executePrepared($prepared, ['tbz', 'bz2', null, null, null, null, null]);
        $database->executePrepared($prepared, ['tar', 'tar', 'archive', 'tar', 'application/x-tar', '^.{257}ustar', 'TAR']);
        $database->executePrepared($prepared, ['7z', '7z', 'archive', '7z', 'application/x-7z-compressed', '^\x37\x7A\xBC\xAF\x27\x1C', '7z']);
        $database->executePrepared($prepared, ['hqx', 'hqx', 'archive', 'binhex', 'application/binhex', '^\(This file must be converted with BinHex', 'Binhex']);
        $database->executePrepared($prepared, ['lzh', 'lzh', 'archive', 'lzh', 'application/x-lzh-compressed', '^.{2}\x2D\x6C\x68', 'LZH']);
        $database->executePrepared($prepared, ['lha', 'lzh', null, null, null, null, null]);
        $database->executePrepared($prepared, ['zip', 'zip', 'archive', 'zip', 'application/zip', '^PK\x03\x04', 'Zip']);
        $database->executePrepared($prepared, ['rar', 'rar', 'archive', 'rar', 'application/x-rar-compressed', '^Rar\x21\x1A\x07\x00', 'RAR']);
        $database->executePrepared($prepared, ['sit', 'sit', 'archive', 'stuffit', 'application/x-stuffit', '^StuffIt \(c\)1997-|StuffIt\!|^SIT\!', 'StuffIt']);
        $database->executePrepared($prepared, ['sitx', 'sit', null, null, null, null, null]);
        $database->executePrepared($prepared, ['iso', 'iso', 'archive', 'iso', 'application/x-iso-image', '^(.{32769}|.{34817}|.{36865})CD001', 'ISO Disk Image']);
        $database->executePrepared($prepared, ['dmg', 'dmg', 'archive', 'dmg', 'application/x-apple-diskimage', 'koly.{508}$', 'Apple Disk Image']);
        $database->executePrepared($prepared, ['', null, 'other', null, null, null, 'Other files']);
        $database->executePrepared($prepared, ['swf', 'swf', 'other', 'flash-shockwave', 'application/x-shockwave-flash', '^CWS|FWS|ZWS', 'Flash/Shockwave']);
        $database->executePrepared($prepared, ['blorb', 'blorb', 'other', 'blorb', 'application/x-blorb', '^FORM.{4}IFRSRIdx', 'Blorb']);
        $database->executePrepared($prepared, ['blb', 'blorb', null, null, null, null, null]);
        $database->executePrepared($prepared, ['gblorb', 'blorb', null, null, null, null, null]);
        $database->executePrepared($prepared, ['glb', 'blorb', null, null, null, null, null]);
        $database->executePrepared($prepared, ['zblorb', 'blorb', null, null, null, null, null]);
        $database->executePrepared($prepared, ['zlb', 'blorb', null, null, null, null, null]);
        $database->executePrepared($prepared, ['tbz', 'bz2', null, null, null, null, null]);
        $database->executePrepared($prepared, ['tbz', 'bz2', null, null, null, null, null]);
        nel_setup_stuff_done(true);
    }

    public function frontEndDefaults()
    {
        $database = nel_database();
        $insert_query = "INSERT INTO " . FRONT_END_TABLE . " (id, resource_type, storage, display_name, location) VALUES (?, ?, ?, ?, ?)";
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, ['nelliel-css', 'css-file', 'file', 'Nelliel', 'nelliel-default/nelliel.css']);
        $database->executePrepared($prepared, ['futaba-css', 'css-file', 'file', 'Futaba', 'nelliel-default/futaba.css']);
        $database->executePrepared($prepared, ['burichan-css', 'css-file', 'file', 'Burichan', 'nelliel-default/burichan.css']);
        $database->executePrepared($prepared, ['nigra-css', 'css-file', 'file', 'Nigra', 'nelliel-default/nigra.css']);
        $database->executePrepared($prepared, ['nelliel-template-basic', 'default-template', 'directory', 'Nelliel Basic Template', 'nelliel_basic']);
        $database->executePrepared($prepared, ['nelliel-filetype-basic', 'filetype-icon-set', 'directory', 'Nelliel Filetype Icon Set', 'nelliel_basic']);
        nel_setup_stuff_done(true);
    }
}