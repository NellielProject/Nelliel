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

    public function versionDefaults()
    {
        $database = nel_database();
        $insert_query = 'INSERT INTO "' . VERSION_TABLE . '" ("item_id", "item_type", "structure_version", "data_version") VALUES (?, ?, ?, ?)';
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, [ASSETS_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [BAN_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [BOARD_DATA_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [BOARD_DEFAULTS_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [CAPTCHA_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [FILETYPE_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [FILE_FILTER_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [LOGINS_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [PERMISSIONS_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [REPORTS_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [ROLES_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [ROLE_PERMISSIONS_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [SITE_CONFIG_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [USER_TABLE, "table", '1', '1']);
        $database->executePrepared($prepared, [USER_ROLE_TABLE, "table", '1', '1']);
        nel_setup_stuff_done(true);
    }

    public function siteConfigDefaults()
    {
        $database = nel_database();
        $insert_query = 'INSERT INTO "' . SITE_CONFIG_TABLE . '" ("config_type", "config_owner", "config_category", "data_type", "config_name", "setting", "select_type") VALUES (?, ?, ?, ?, ?, ?, ?)';
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'general', 'string', 'home_page', '/', 0]);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'crypt', 'string', 'post_password_algorithm', 'sha256', 0]);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'crypt', 'string', 'secure_tripcode_algorithm', 'sha256', 0]);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'crypt', 'boolean', 'do_password_rehash', '0', 0]);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'output', 'string', 'index_filename_format', 'index-%d', 0]);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'output', 'string', 'thread_filename_format', 'thread-%d', 0]);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'general', 'boolean', 'template_id', 'nelliel-template-basic', 0]);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'general', 'string', 'language', 'en-US', 0]);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'general', 'string', 'recaptcha_site_key', '', 0]);
        $database->executePrepared($prepared, ['core_setting', 'nelliel', 'general', 'string', 'recaptcha_sekrit_key', '', 0]);
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

    public function rolePermissionsDefaults()
    {
        $database = nel_database();
        $insert_query = 'INSERT INTO "' . ROLE_PERMISSIONS_TABLE . '" ("role_id", "perm_id", "perm_setting") VALUES (?, ?, ?)';
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_site_config_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_site_config_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_board_defaults_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_board_defaults_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_board_config_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_board_config_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_board_config_lock_override', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_user_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_user_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_role_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_role_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_ban_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_ban_modify', 1]);
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
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_manage_boards_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_extract_gettext', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_file_filters_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_file_filters_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_templates_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_templates_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_filetypes_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_filetypes_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_styles_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_styles_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_permissions_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_permissions_modify', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_icon_sets_access', 1]);
        $database->executePrepared($prepared, ['SUPER_ADMIN', 'perm_icon_sets_modify', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_site_config_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_site_config_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_board_defaults_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_board_defaults_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_board_config_access', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_board_config_modify', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_board_config_lock_override', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_user_access', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_user_modify', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_role_access', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_role_modify', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_ban_access', 1]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_ban_modify', 1]);
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
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_manage_boards_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_extract_gettext', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_file_filters_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_file_filters_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_templates_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_templates_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_filetypes_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_filetypes_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_styles_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_styles_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_permissions_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_permissions_modify', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_icon_sets_access', 0]);
        $database->executePrepared($prepared, ['BOARD_ADMIN', 'perm_icon_sets_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_site_config_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_site_config_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_board_defaults_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_board_defaults_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_board_config_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_board_config_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_board_config_lock_override', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_user_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_user_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_role_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_role_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_ban_access', 1]);
        $database->executePrepared($prepared, ['MOD', 'perm_ban_modify', 1]);
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
        $database->executePrepared($prepared, ['MOD', 'perm_manage_boards_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_extract_gettext', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_file_filters_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_file_filters_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_templates_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_templates_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_filetypes_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_filetypes_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_styles_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_styles_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_permissions_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_permissions_modify', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_icon_sets_access', 0]);
        $database->executePrepared($prepared, ['MOD', 'perm_icon_sets_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_site_config_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_site_config_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_board_defaults_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_board_defaults_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_board_config_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_board_config_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_board_config_lock_override', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_user_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_user_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_role_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_role_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_ban_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_ban_modify', 0]);
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
        $database->executePrepared($prepared, ['JANITOR', 'perm_manage_boards_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_extract_gettext', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_file_filters_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_file_filters_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_templates_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_templates_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_filetypes_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_filetypes_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_styles_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_styles_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_permissions_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_permissions_modify', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_icon_sets_access', 0]);
        $database->executePrepared($prepared, ['JANITOR', 'perm_icon_sets_modify', 0]);
        nel_setup_stuff_done(true);
    }

    public function permissionsDefaults()
    {
        $database = nel_database();
        $insert_query = 'INSERT INTO "' . PERMISSIONS_TABLE . '" ("permission", "description") VALUES (?, ?)';
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, ['perm_site_config_access', 'Access the Site Settings panel']);
        $database->executePrepared($prepared, ['perm_site_config_modify', 'Modify site settings']);
        $database->executePrepared($prepared, ['perm_board_defaults_access', 'Access the Board Defaults panel']);
        $database->executePrepared($prepared, ['perm_board_defaults_modify', 'Modify board defaults']);
        $database->executePrepared($prepared, ['perm_board_config_access', 'Access the Board Settings panel']);
        $database->executePrepared($prepared, ['perm_board_config_modify', 'Modify board settings']);
        $database->executePrepared($prepared, ['perm_board_config_lock_override', 'Override board config lock']);
        $database->executePrepared($prepared, ['perm_user_access', 'Access the Users panel']);
        $database->executePrepared($prepared, ['perm_user_modify', 'Modify users']);
        $database->executePrepared($prepared, ['perm_role_access', 'Access the Roles panel']);
        $database->executePrepared($prepared, ['perm_role_modify', 'Modify roles']);
        $database->executePrepared($prepared, ['perm_ban_access', 'Access the Bans panel']);
        $database->executePrepared($prepared, ['perm_ban_modify', 'Modify bans']);
        $database->executePrepared($prepared, ['perm_modmode_access', 'Access to Moderator Mode']);
        $database->executePrepared($prepared, ['perm_threads_access', 'Access the Threads panel']);
        $database->executePrepared($prepared, ['perm_threads_modify', 'Modify threads and posts']);
        $database->executePrepared($prepared, ['perm_post_delete', 'Delete posts']);
        $database->executePrepared($prepared, ['perm_post_as_staff', 'Post as staff']);
        $database->executePrepared($prepared, ['perm_post_in_locked', 'Post in locked thread']);
        $database->executePrepared($prepared, ['perm_post_sticky', 'Sticky/unsticky posts and threads']);
        $database->executePrepared($prepared, ['perm_post_lock', 'Lock/unlock threads']);
        $database->executePrepared($prepared, ['perm_post_mod_comment', 'Add staff commentary to a post']);
        $database->executePrepared($prepared, ['perm_reports_access', 'Access the Reports panel']);
        $database->executePrepared($prepared, ['perm_reports_dismiss', 'Dismiss reports']);
        $database->executePrepared($prepared, ['perm_regen_cache', 'Regenerate caches']);
        $database->executePrepared($prepared, ['perm_regen_pages', 'Regenerate pages']);
        $database->executePrepared($prepared, ['perm_manage_boards_access', 'Access the Manage Boards panel']);
        $database->executePrepared($prepared, ['perm_manage_boards_modify', 'Modify boards']);
        $database->executePrepared($prepared, ['perm_extract_gettext', 'Extract Gettext strings']);
        $database->executePrepared($prepared, ['perm_file_filters_access', 'Access the File Filters panel']);
        $database->executePrepared($prepared, ['perm_file_filters_modify', 'Modify file filters']);
        $database->executePrepared($prepared, ['perm_templates_access', 'Access the Templates panel']);
        $database->executePrepared($prepared, ['perm_templates_modify', 'Modify templates']);
        $database->executePrepared($prepared, ['perm_filetypes_access', 'Access the Filetypes panel']);
        $database->executePrepared($prepared, ['perm_filetypes_modify', 'Modify filetypes']);
        $database->executePrepared($prepared, ['perm_styles_access', 'Access the Styles panel']);
        $database->executePrepared($prepared, ['perm_styles_modify', 'Modify styles']);
        $database->executePrepared($prepared, ['perm_permissions_access', 'Access the Permissions panel']);
        $database->executePrepared($prepared, ['perm_permissions_modify', 'Modify permissions']);
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
        " (user_id, user_password, active, last_login) VALUES (?, ?, ?, ?)";
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, [DEFAULTADMIN, nel_password_hash(DEFAULTADMIN_PASS, NEL_PASSWORD_ALGORITHM), 1, 0]);
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
        $insert_query = 'INSERT INTO "' . $config_table .
        '" ("config_type", "config_owner", "config_category", "data_type", "config_name", "setting", "select_type", "edit_lock") VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'allow_tripkeys', '1', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'force_anonymous', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'show_title', '1', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'show_favicon', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'show_logo', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'use_thumb', '1', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'use_magick', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'use_file_icon', '1', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'use_png_thumb', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'animated_gif_preview', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'require_image_start', '1', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'require_image_always', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'allow_multifile', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'allow_op_multifile', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'use_fgsfds', '1', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'use_honeypot', '1', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'only_thread_duplicates', '1', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'only_op_duplicates', '1', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'string', 'board_name', 'Nelliel-powered image board', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'string', 'board_favicon', '', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'string', 'board_logo', '', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'string', 'language', 'en-US', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'thread_delay', '120', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'reply_delay', '60', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'abbreviate_thread', '5', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_post_files', '3', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_files_row', '3', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_multi_width', '175', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_multi_height', '175', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'jpeg_quality', '90', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_width', '256', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_height', '256', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_filesize', '4096', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_name_length', '100', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_email_length', '100', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_subject_length', '100', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_comment_length', '5000', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_comment_lines', '60', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'comment_display_lines', '15', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_source_length', '255', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_license_length', '255', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'threads_per_page', '10', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'page_limit', '10', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'page_buffer', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_posts', '1000', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'max_bumps', '1000', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'string', 'tripkey_marker', '!', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'string', 'date_format', 'Y/m/d (D) H:i:s', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'string', 'old_threads', 'ARCHIVE', 1, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'string', 'fgsfds_name', 'FGSFDS', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'string', 'indent_marker', '>>', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'file_sha256', '1', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'file_sha512', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'enable_dynamic_pages', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'string', 'template_id', 'nelliel-template', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'string', 'filetype_icon_set_id', 'filetype-nelliel-basic', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'timestamp_filename', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'use_captcha', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'boolean', 'use_recaptcha', '0', 0, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'string', 'recaptcha_type', 'CHECKBOX', 1, 0]);
        $database->executePrepared($prepared, ['board_setting', 'nelliel', 'general', 'integer', 'poster_id_length', '6', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'graphics', '1', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'jpeg', '1', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'gif', '1', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'png', '1', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'jpeg2000', '1', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'tiff', '1', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'bmp', '1', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'icon', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'photoshop', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'tga', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'pict', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'art', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'cel', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'kcf', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'ani', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'icns', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'illustrator', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'postscript', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'graphics', 'boolean', 'eps', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'boolean', 'audio', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'boolean', 'wave', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'boolean', 'aiff', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'boolean', 'mp3', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'boolean', 'm4a', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'boolean', 'flac', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'boolean', 'aac', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'boolean', 'ogg-audio', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'boolean', 'au', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'boolean', 'wma', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'boolean', 'midi', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'audio', 'boolean', 'ac3', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'boolean', 'video', '1', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'boolean', 'mpeg', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'boolean', 'quicktime', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'boolean', 'avi', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'boolean', 'wmv', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'boolean', 'mpeg4', '1', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'boolean', 'mkv', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'boolean', 'flv', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'boolean', 'webm', '1', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'boolean', '3gp', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'boolean', 'ogg-video', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'video', 'boolean', 'm4v', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'boolean', 'document', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'boolean', 'rtf', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'boolean', 'pdf', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'boolean', 'msword', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'boolean', 'powerpoint', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'boolean', 'msexcel', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'document', 'boolean', 'txt', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'boolean', 'archive', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'boolean', 'gzip', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'boolean', 'bzip2', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'boolean', 'binhex', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'boolean', 'lzh', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'boolean', 'zip', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'boolean', 'rar', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'boolean', 'stuffit', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'boolean', 'tar', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'boolean', '7z', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'boolean', 'iso', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'archive', 'boolean', 'dmg', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'other', 'boolean', 'other', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'other', 'boolean', 'swf', '0', 0, 0]);
        $database->executePrepared($prepared, ['filetype_enable', 'nelliel', 'other', 'boolean', 'blorb', '0', 0, 0]);
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
        $database->executePrepared($prepared, ['eps', 'eps', 'graphics', 'eps', 'application/postscript', '^\xC5\xD0\xD3\xC6|%!PS-Adobe-[0-9]\.[0-9] EPSF-[0-9]\.[0-9]', 'Encapsulated PostScript']);
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
        $database->executePrepared($prepared, ['mkv', 'mkv', 'video', 'mkv', 'video/x-matroska', '^\x1A\x45\xDF\xA3', 'Matroska Media']);
        $database->executePrepared($prepared, ['flv', 'flv', 'video', 'flv', 'video/x-flv', '^FLV\x01', 'Flash Video']);
        $database->executePrepared($prepared, ['webm', 'webm', 'video', 'webm', 'video/webm', '^\x1A\x45\xDF\xA3', 'WebM']);
        $database->executePrepared($prepared, ['3gp', '3gp', 'video', '3gp', 'video/3gpp', '^.{4}ftyp3gp', '3GP']);
        $database->executePrepared($prepared, ['ogv', 'ogv', 'video', 'ogg-video', 'video/ogg', '^OggS', 'Ogg Video']);
        $database->executePrepared($prepared, ['', null, 'document', null, null, null, 'Text and document files']);
        $database->executePrepared($prepared, ['rtf', 'rtf', 'document', 'rtf', 'application/rtf', '^\x7B\x5C\x72\x74\x66\x31', 'Rich Text']);
        $database->executePrepared($prepared, ['pdf', 'pdf', 'document', 'pdf', 'application/pdf', '^\x25PDF', 'PDF']);
        $database->executePrepared($prepared, ['doc', 'doc', 'document', 'msword', 'application/msword', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1|^\xDB\xA5\x2D\x00|^PK\x03\x04', 'Microsoft Word']);
        $database->executePrepared($prepared, ['docx', 'doc', null, null, null, null, null]);
        $database->executePrepared($prepared, ['ppt', 'ppt', 'document', 'powerpoint', 'application/ms-powerpoint', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1|^PK\x03\x04', 'PowerPoint']);
        $database->executePrepared($prepared, ['pptx', 'ppt', null, null, null, null, null]);
        $database->executePrepared($prepared, ['xls', 'xls', 'document', 'msexcel', 'application/ms-excel', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1|^PK\x03\x04', 'Microsoft Excel']);
        $database->executePrepared($prepared, ['xlsx', 'xls', null, null, null, null, null]);
        $database->executePrepared($prepared, ['txt', 'txt', 'document', 'txt', 'text/plain', '', 'Plaintext']);
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
        $database->executePrepared($prepared, ['swf', 'swf', 'other', 'swf', 'application/x-shockwave-flash', '^CWS|FWS|ZWS', 'Flash/Shockwave']);
        $database->executePrepared($prepared, ['blorb', 'blorb', 'other', 'blorb', 'application/x-blorb', '^FORM.{4}IFRSRIdx', 'Blorb']);
        $database->executePrepared($prepared, ['blb', 'blorb', null, null, null, null, null]);
        $database->executePrepared($prepared, ['gblorb', 'blorb', null, null, null, null, null]);
        $database->executePrepared($prepared, ['glb', 'blorb', null, null, null, null, null]);
        $database->executePrepared($prepared, ['zblorb', 'blorb', null, null, null, null, null]);
        $database->executePrepared($prepared, ['zlb', 'blorb', null, null, null, null, null]);
        nel_setup_stuff_done(true);
    }

    public function templatesDefaults()
    {
        $database = nel_database();
        $insert_query = "INSERT INTO " . TEMPLATES_TABLE . " (id, is_default, info) VALUES (?, ?, ?)";
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, ['template-nelliel-basic', 1,
        '{"id": "template-nelliel-basic","directory": "nelliel_basic","name": "Nelliel Basic Template","version": "1.0","description": "The basic template for Nelliel.","output_type": "html"}']);
        nel_setup_stuff_done(true);
    }

    public function assetDefaults()
    {
        $database = nel_database();
        $insert_query = "INSERT INTO " . ASSETS_TABLE . " (id, type, is_default, info) VALUES (?, ?, ?, ?)";
        $prepared = $database->prepare($insert_query);
        $database->executePrepared($prepared, ['filetype-nelliel-basic', 'icon-set', 1,
        '{"id": "filetype-nelliel-basic","directory": "filetype_nelliel_basic","name": "Nelliel Basic Filetype Icon Set","set_type": "filetype","version": "1.0","description": "The basic filetype icon set for Nelliel."}']);
        $database->executePrepared($prepared, ['style-nelliel', 'style', 1,
        '{"id": "style-nelliel","directory": "nelliel","main_file": "nelliel.css","name": "Nelliel","version": "1.0","description": "Nelliel style","style_type": "css"}']);
        $database->executePrepared($prepared, ['style-nelliel-b', 'style', 0,
        '{"id": "style-nelliel-b","directory": "nelliel_b","main_file": "nelliel_b.css","name": "Nelliel B","version": "1.0","description": "Nelliel B style","style_type": "css"}']);
        $database->executePrepared($prepared, ['style-futaba', 'style', 0,
        '{"id": "style-futaba","directory": "futaba","main_file": "futaba.css","name": "Futaba","version": "1.0","description": "Futaba style","style_type": "css"}']);
        $database->executePrepared($prepared, ['style-burichan', 'style', 0,
        '{"id": "style-burichan","directory": "burichan","main_file": "burichan.css","name": "Burichan","version": "1.0","description": "Burichan style","style_type": "css"}']);
        $database->executePrepared($prepared, ['style-nigra', 'style', 0,
        '{"id": "style-nigra","directory": "nigra","main_file": "nigra.css","name": "Nigra","version": "1.0","description": "Nigra style","style_type": "css"}']);
        nel_setup_stuff_done(true);
    }
}