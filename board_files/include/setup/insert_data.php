<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_insert_site_config_defaults()
{
    $dbh = nel_database();
    $insert_into = "INSERT INTO " . SITE_CONFIG_TABLE .
         " (config_type, config_owner, config_category, data_type, config_name, setting) VALUES ";
    $dbh->query($insert_into . "('core_setting', 'nelliel', 'general', 'str', 'home_page', '../')");
    $dbh->query($insert_into . "('core_setting', 'nelliel', 'crypt', 'str', 'post_password_algorithm', 'sha256')");
    $dbh->query($insert_into . "('core_setting', 'nelliel', 'crypt', 'str', 'secure_tripcode_algorithm', 'sha256')");
    $dbh->query($insert_into . "('core_setting', 'nelliel', 'crypt', 'bool', 'do_password_rehash', '0')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'original_bans_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'current_bans_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'original_user_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'current_user_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'original_roles_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'current_roles_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'original_user_role_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'current_user_role_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'original_permissions_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'current_permissions_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'original_logins_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'current_logins_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'original_board_data_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'current_board_data_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'original_site_config_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'current_site_config_schema', '001')");
    nel_setup_stuff_done(true);
}

function nel_insert_role_defaults()
{
    $dbh = nel_database();
    $insert_into = "INSERT INTO " . ROLES_TABLE . " (role_id, role_level, role_title, capcode_text) VALUES ";
    $dbh->query($insert_into . "('SUPER_ADMIN', 1000, 'Site Administrator', '## Site Administrator ##')");
    $dbh->query($insert_into . "('BOARD_ADMIN', 100, 'Board Administrator', '## Board Administrator ##')");
    $dbh->query($insert_into . "('MOD', 50, 'Moderator', '## Moderator ##')");
    $dbh->query($insert_into . "('JANITOR', 10, 'Janitor', '## Janitor ##')");
    nel_setup_stuff_done(true);
}

function nel_insert_permissions_defaults()
{
    $dbh = nel_database();
    $insert_into = "INSERT INTO " . PERMISSIONS_TABLE . " (role_id, perm_id, perm_setting) VALUES ";
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_config_access', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_config_modify', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_user_access', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_user_add', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_user_modify', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_user_delete', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_user_change_pass', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_role_access', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_role_add', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_role_modify', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_role_delete', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_ban_access', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_ban_add', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_ban_modify', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_ban_delete', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_access', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_modify', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_delete', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_file_delete', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_default_name', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_custom_name', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_override_anon', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_sticky', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_unsticky', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_lock', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_unlock', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_in_locked', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_comment', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_post_permsage', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_regen_caches', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_regen_index', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_regen_threads', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_modmode_access', 1)");
    $dbh->query($insert_into . "('SUPER_ADMIN', 'perm_modmode_view_ips', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_config_access', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_config_modify', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_user_access', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_user_add', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_user_modify', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_user_delete', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_user_change_pass', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_role_access', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_role_add', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_role_modify', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_role_delete', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_ban_access', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_ban_add', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_ban_modify', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_ban_delete', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_access', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_modify', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_delete', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_file_delete', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_default_name', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_custom_name', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_override_anon', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_sticky', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_unsticky', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_lock', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_unlock', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_in_locked', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_comment', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_post_permsage', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_regen_caches', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_regen_index', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_regen_threads', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_modmode_access', 1)");
    $dbh->query($insert_into . "('BOARD_ADMIN', 'perm_modmode_view_ips', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_config_access', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_config_change', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_user_access', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_user_add', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_user_modify', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_user_delete', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_user_change_pass', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_role_access', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_role_add', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_role_modify', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_role_delete', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_ban_access', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_ban_add', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_ban_modify', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_ban_delete', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_post_access', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_post_modify', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_post_delete', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_post_file_delete', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_post_default_name', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_post_custom_name', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_post_override_anon', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_post_sticky', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_post_unsticky', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_post_lock', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_post_unlock', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_post_in_locked', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_post_comment', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_post_permsage', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_regen_caches', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_regen_index', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_regen_threads', 0)");
    $dbh->query($insert_into . "('MOD', 'perm_modmode_access', 1)");
    $dbh->query($insert_into . "('MOD', 'perm_modmode_view_ips', 1)");
    $dbh->query($insert_into . "('JANITOR', 'perm_config_access', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_config_change', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_user_access', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_user_add', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_user_modify', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_user_delete', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_user_change_pass', 1)");
    $dbh->query($insert_into . "('JANITOR', 'perm_role_access', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_role_add', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_role_modify', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_role_delete', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_ban_access', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_ban_add', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_ban_modify', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_ban_delete', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_access', 1)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_modify', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_delete', 1)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_file_delete', 1)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_default_name', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_custom_name', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_override_anon', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_sticky', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_unsticky', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_lock', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_unlock', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_in_locked', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_comment', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_post_permsage', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_regen_caches', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_regen_index', 0)");
    $dbh->query($insert_into . "('JANITOR', 'perm_regen_threads', 0");
    $dbh->query($insert_into . "('JANITOR', 'perm_modmode_access', 1)");
    $dbh->query($insert_into . "('JANITOR', 'perm_modmode_view_ips', 0)");
    nel_setup_stuff_done(true);
}

function nel_insert_default_admin()
{
    if (DEFAULTADMIN === '' || DEFAULTADMIN_PASS === '')
    {
        return false;
    }

    $dbh = nel_database();
    $result = $dbh->query('SELECT 1 FROM "' . USER_TABLE . '" WHERE "user_id" = \'' . DEFAULTADMIN . '\'');

    if ($result->fetch() !== false)
    {
        return false;
    }

    $result = $dbh->query('INSERT INTO "' . USER_TABLE . '" (user_id, user_password, active, failed_logins, last_failed_login)
    VALUES (\'' .
         DEFAULTADMIN . '\', \'' . nel_password_hash(DEFAULTADMIN_PASS, NEL_PASSWORD_ALGORITHM) . '\', 1, 1, 0)');

    nel_setup_stuff_done($result);
}

function nel_insert_default_admin_role()
{
    if (DEFAULTADMIN === '' || DEFAULTADMIN_PASS === '')
    {
        echo "wat1";
        return false;
    }

    $dbh = nel_database();
    $result = $dbh->query('SELECT 1 FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = \'' . DEFAULTADMIN . '\'');

    if ($result->fetch() !== false)
    {
        echo "wat2";
        return false;
    }

    $result = $dbh->query("INSERT INTO \"" . USER_ROLE_TABLE . "\" (user_id, role_id, all_boards) VALUES ('" .
         DEFAULTADMIN . "', 'SUPER_ADMIN', 1)");

    nel_setup_stuff_done($result);
}

function nel_insert_board_config_defaults($config_table)
{
    $dbh = nel_database();
    $insert_into = "INSERT INTO " . $config_table .
         " (config_type, config_owner, config_category, data_type, config_name, setting) VALUES ";
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'original_posts_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'current_posts_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'original_threads_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'current_threads_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'original_files_schema', '001')");
    $dbh->query($insert_into . "('schema_version', 'nelliel', 'database', 'str', 'current_files_schema', '001')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'allow_tripkeys', '1')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'force_anonymous', '0')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'show_title', '1')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'show_favicon', '0')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'show_logo', '0')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'use_thumb', '1')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'use_magick', '0')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'use_file_icon', '1')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'use_png_thumb', '0')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'animated_gif_preview', '0')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'require_image_start', '1')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'require_image_always', '0')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'allow_multifile', '0')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'allow_op_multifile', '0')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'use_new_imgdel', '1')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'use_fgsfds', '1')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'use_spambot_trap', '1')");
    $dbh->query($insert_into .
         "('board_setting', 'nelliel', 'general', 'str', 'board_name', 'Nelliel-powered image board')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'str', 'board_favicon', '')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'str', 'board_logo', '')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'board_language', 'en-us')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'thread_delay', '60')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'reply_delay', '15')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'abbreviate_thread', '5')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_post_files', '3')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_files_row', '3')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_multi_width', '175')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_multi_height', '175')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'jpeg_quality', '85')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_width', '256')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_height', '256')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_filesize', '1024')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_name_length', '100')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_email_length', '100')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_subject_length', '100')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_comment_length', '5000')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_comment_lines', '60')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'comment_display_lines', '15')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_source_length', '255')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_license_length', '255')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'threads_per_page', '10')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'page_limit', '10')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'page_buffer', '0')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_posts', '1000')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'int', 'max_bumps', '750')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'str', 'tripkey_marker', '!')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'str', 'date_format', 'ISO')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'str', 'old_threads', 'ARCHIVE')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'str', 'date_separator', '/')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'str', 'fgsfds_name', 'FGSFDS')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'str', 'indent_marker', '>>')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'file_sha256', '1')");
    $dbh->query($insert_into . "('board_setting', 'nelliel', 'general', 'bool', 'file_sha512', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'graphics', '1')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'jpeg', '1')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'gif', '1')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'png', '1')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'jpeg2000', '1')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'tiff', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'bmp', '1')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'icon', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'photoshop', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'tga', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'pict', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'art', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'cel', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'graphics', 'bool', 'kcf', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'audio', 'bool', 'audio', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'audio', 'bool', 'wave', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'audio', 'bool', 'aiff', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'audio', 'bool', 'mp3', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'audio', 'bool', 'm4a', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'audio', 'bool', 'flac', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'audio', 'bool', 'aac', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'audio', 'bool', 'ogg-audio', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'audio', 'bool', 'au', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'audio', 'bool', 'wma', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'audio', 'bool', 'midi', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'audio', 'bool', 'ac3', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'video', 'bool', 'video', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'video', 'bool', 'mpeg', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'video', 'bool', 'quicktime', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'video', 'bool', 'avi', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'video', 'bool', 'wmv', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'video', 'bool', 'mpeg4', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'video', 'bool', 'matroska', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'video', 'bool', 'flash-video', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'video', 'bool', 'webm', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'video', 'bool', '3gp', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'video', 'bool', 'ogg-video', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'video', 'bool', 'm4v', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'document', 'bool', 'document', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'document', 'bool', 'rich-text', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'document', 'bool', 'pdf', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'document', 'bool', 'msword', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'document', 'bool', 'powerpoint', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'document', 'bool', 'msexcel', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'document', 'bool', 'plaintext', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'archive', 'bool', 'archive', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'archive', 'bool', 'gzip', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'archive', 'bool', 'bzip2', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'archive', 'bool', 'binhex', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'archive', 'bool', 'lzh', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'archive', 'bool', 'zip', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'archive', 'bool', 'rar', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'archive', 'bool', 'stuffit', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'archive', 'bool', 'tar', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'archive', 'bool', '7z', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'archive', 'bool', 'iso', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'archive', 'bool', 'dmg', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'other', 'bool', 'other', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'other', 'bool', 'flash-shockwave', '0')");
    $dbh->query($insert_into . "('filetype_enable', 'nelliel', 'other', 'bool', 'blorb', '0')");
    nel_setup_stuff_done(true);
}

function nel_insert_filetypes()
{
    $dbh = nel_database();
    $insert_into = "INSERT INTO " . FILETYPE_TABLE .
         " (extension, parent_extension, type, format, mime, id_regex, label) VALUES ";
    // Slash Mania!
    $dbh->query($insert_into . "('jpg', 'jpg', 'graphics', 'jpeg', 'image/jpeg', '^\\\\xFF\\\\xD8\\\\xFF', 'JPEG')");
    $dbh->query($insert_into . "('jpeg', 'jpg', null, null, null, null, null)");
    $dbh->query($insert_into . "('jpe', 'jpg', null, null, null, null, null)");
    $dbh->query($insert_into . "('gif', 'gif', 'graphics', 'gif', 'image/gif', '^(?:GIF87a|GIF89a)', 'GIF')");
    $dbh->query($insert_into .
         "('png', 'png', 'graphics', 'png', 'image/png', '^\\\\x89\\\\x50\\\\x4E\\\\x47\\\\x0D\\\\x0A\\\\x1A\\\\x0A', 'PNG')");
    $dbh->query($insert_into .
         "('jp2', 'jp2', 'graphics', 'jpeg2000', 'image/jp2', '^\\\\x00\\\\x00\\\\x00\\\\x0C\\\\x6A\\\\x50\\\\x20\\\\x20\\\\x0D\\\\x0A', 'JPEG2000')");
    $dbh->query($insert_into . "('j2k', 'jp2', null, null, null, null, null)");
    $dbh->query($insert_into .
         "('tiff', 'tiff', 'graphics', 'tiff', 'image/tiff', '^I\\\\x20?I\\\\x2A\\\\x00|^MM\\\\x00[\\\\x2A-\\\\x2B]', 'TIFF')");
    $dbh->query($insert_into . "('tif', 'tiff', null, null, null, null, null)");
    $dbh->query($insert_into . "('bmp', 'bmp', 'graphics', 'bmp', 'image/x-bmp', '^BM', 'BMP')");
    $dbh->query($insert_into .
         "('ico', 'ico', 'graphics', 'icon', 'image/x-icon', '^\\\\x00\\\\x00\\\\x01\\\\x00', 'Icon')");
    $dbh->query($insert_into .
         "('psd', 'psd', 'graphics', 'photoshop', 'image/vnd.adobe.photoshop', '^8BPS\\\\x00\\\\x01', 'PSD (Photoshop)')");
    $dbh->query($insert_into . "('tga', 'tga', 'graphics', 'tga', 'image/x-targa', '^.{1}\\\\x00', 'Truevision TGA')");
    $dbh->query($insert_into .
         "('pict', 'pict', 'graphics', 'pict', 'image/x-pict', '^.{522}(?:\\\\x11\\\\x01|\\\\x00\\\\x11\\\\x02\\\\xFF\\\\x0C\\\\x00)', 'PICT')");
    $dbh->query($insert_into . "('pct', 'pict', null, null, null, null, null)");
    $dbh->query($insert_into .
         "('art', 'art', 'graphics', 'art', 'image/x-jg', '^JG[\\\\x03-\\\\x04]\\\\x0E', 'AOL ART')");
    $dbh->query($insert_into .
         "('cel', 'cel', 'graphics', 'cel', 'application/octet-stream', '^KiSS(?:\\\\x20\\\\x04|\\\\x20\\\\x08|\\\\x21\\\\x20|\\\\x20\\\\x20)', 'Kisekae CEL')");
    $dbh->query($insert_into .
         "('kcf', 'kcf', 'graphics', 'kcf', 'application/octet-stream', '^KiSS\\\\x10)', 'Kisekae Pallete')");
    $dbh->query($insert_into . "('wav', 'wav', 'audio', 'wave', 'audio/x-wave', '^RIFF.{4}WAVEfmt', 'WAVE')");
    $dbh->query($insert_into . "('aif', 'aif', 'audio', 'aiff', 'audio/aiff', '^FORM.{4}AIFF', 'AIFF')");
    $dbh->query($insert_into . "('aiff', 'aif', null, null, null, null, null)");
    $dbh->query($insert_into . "('mp3', 'mp3', 'audio', 'mp3', 'audio/mpeg', '^ID3|\\\\xFF[\\\\xE0-\\\\xFF]{1}', 'MP3')");
    $dbh->query($insert_into . "('m4a', 'm4a', 'audio', 'm4a', 'audio/m4a', '^.{4}ftypM4A', 'MPEG-4 Audio')");
    $dbh->query($insert_into .
         "('flac', 'flac', 'audio', 'flac', 'audio/x-flac', '^fLaC\\\\x00\\\\x00\\\\x00\\\\x22', 'FLAC')");
    $dbh->query($insert_into . "('aac', 'aac', 'audio', 'aac', 'audio/aac', '^ADIF|^\\\\xFF(?:\\\\xF1|\\\\xF9)', 'AAC')");
    $dbh->query($insert_into . "('ogg', 'ogg', 'audio', 'ogg-audio', 'audio/ogg', '^OggS', 'OGG Audio')");
    $dbh->query($insert_into . "('oga', 'ogg', null, null, null, null, null)");
    $dbh->query($insert_into . "('au', 'au', 'audio', 'au', 'audio/basic', '^\.snd', 'AU')");
    $dbh->query($insert_into . "('snd', 'au', null, null, null, null, null)");
    $dbh->query($insert_into . "('ac3', 'ac3', 'audio', 'ac3', 'audio/ac3', '^\\\\x0B\\\\x77', 'AC3')");
    $dbh->query($insert_into .
         "('wma', 'wma', 'audio', 'wma', 'audio/x-ms-wma', '^\\\\x30\\\\x26\\\\xB2\\\\x75\\\\x8E\\\\x66\\\\xCF\\\\x11\\\\xA6\\\\xD9\\\\x00\\\\xAA\\\\x00\\\\x62\\\\xCE\\\\x6C', 'Windows Media Audio')");
    $dbh->query($insert_into . "('midi', 'midi', 'audio', 'midi', 'audio/midi', '^MThd', 'MIDI')");
    $dbh->query($insert_into . "('mid', 'midi', null, null, null, null, null)");
    $dbh->query($insert_into .
         "('mpg', 'mpg', 'video', 'mpeg', 'video/mpeg', '^\\\\x00\\\\x00\\\\x01[\\\\xB0-\\\\xBF]', 'MPEG-1/MPEG-2')");
    $dbh->query($insert_into . "('mpeg', 'mpg', null, null, null, null, null)");
    $dbh->query($insert_into . "('mpe', 'mpg', null, null, null, null, null)");
    $dbh->query($insert_into .
         "('mov', 'mov', 'video', 'quicktime', 'video/quicktime', '^.{4}(?:cmov|free|ftypqt|mdat|moov|pnot|skip|wide)', 'Quicktime Movie')");
    $dbh->query($insert_into . "('avi', 'avi', 'video', 'avi', 'video/x-msvideo', '^RIFF.{4}AVI\\\\x20LIST', 'AVI')");
    $dbh->query($insert_into .
         "('wmv', 'wmv', 'video', 'wmv', 'video/x-ms-wmv', '^\\\\x30\\\\x26\\\\xB2\\\\x75\\\\x8E\\\\x66\\\\xCF\\\\x11\\\\xA6\\\\xD9\\\\x00\\\\xAA\\\\x00\\\\x62\\\\xCE\\\\x6C', 'Windows Media Video')");
    $dbh->query($insert_into .
         "('mp4', 'mp4', 'video', 'mpeg4', 'video/mp4', '^.{4}ftyp(?:iso2|isom|mp41|mp42)', 'MPEG-4 Media')");
    $dbh->query($insert_into .
         "('m4v', 'm4v', 'video', 'm4v', 'video/x-m4v', '^.{4}ftypmp(?:41|42|71)', 'MPEG-4 Video')");
    $dbh->query($insert_into .
         "('mkv', 'mkv', 'video', 'matroska', 'video/x-matroska', '^\\\\x1A\\\\x45\\\\xDF\\\\xA3', 'Matroska Media')");
    $dbh->query($insert_into . "('flv', 'flv', 'video', 'flash-video', 'video/x-flv', '^FLV\\\\x01', 'Flash Video')");
    $dbh->query($insert_into .
         "('webm', 'webm', 'video', 'webm', 'video/webm', '^\\\\x1A\\\\x45\\\\xDF\\\\xA3', 'WebM')");
    $dbh->query($insert_into . "('3gp', '3gp', 'video', '3gp', 'video/3gpp', '^.{4}ftyp3gp', '3GP')");
    $dbh->query($insert_into . "('ogv', 'ogv', 'video', 'ogg-video', 'video/ogg', '^OggS', 'Ogg Video')");
    $dbh->query($insert_into .
         "('rtf', 'rtf', 'document', 'rich-text', 'application/rtf', '^\\\\x7B\\\\x5C\\\\x72\\\\x74\\\\x66\\\\x31', 'Rich Text')");
    $dbh->query($insert_into . "('pdf', 'pdf', 'document', 'pdf', 'application/pdf', '^\\\\x25PDF', 'PDF')");
    $dbh->query($insert_into .
         "('doc', 'doc', 'document', 'msword', 'application/msword', '^\\\\xD0\\\\xCF\\\\x11\\\\xE0\\\\xA1\\\\xB1\\\\x1A\\\\xE1|\\\\xDB\\\\xA5\\\\x2D\\\\x00', 'Microsoft Word')");
    $dbh->query($insert_into .
         "('ppt', 'ppt', 'document', 'powerpoint', 'application/ms-powerpoint', '^\\\\xD0\\\\xCF\\\\x11\\\\xE0\\\\xA1\\\\xB1\\\\x1A\\\\xE1', 'PowerPoint')");
    $dbh->query($insert_into .
         "('xls', 'xls', 'document', 'msexcel', 'application/ms-excel', '^\\\\xD0\\\\xCF\\\\x11\\\\xE0\\\\xA1\\\\xB1\\\\x1A\\\\xE1', 'Microsoft Excel')");
    $dbh->query($insert_into . "('txt', 'txt', 'document', 'plaintext', 'text/plain', '', 'Plaintext')");
    $dbh->query($insert_into . "('gz', 'gz', 'archive', 'gzip', 'application/gzip', '^\\\\x1F\\\\x8B\\\\x08', 'GZip')");
    $dbh->query($insert_into . "('tgz', 'gz', null, null, null, null, null)");
    $dbh->query($insert_into . "('gzip', 'gz', null, null, null, null, null)");
    $dbh->query($insert_into .
         "('bz2', 'bz2', 'archive', 'bzip2', 'application/x-bzip2', '^BZh.{1}\\\\x31\\\\x41\\\\x59\\\\x26\\\\x53\\\\x59', 'bzip2')");
    $dbh->query($insert_into . "('tbz2', 'bz2', null, null, null, null, null)");
    $dbh->query($insert_into . "('tbz', 'bz2', null, null, null, null, null)");
    $dbh->query($insert_into . "('tar', 'tar', 'archive', 'tar', 'application/x-tar', '^.{257}ustar', 'TAR')");
    $dbh->query($insert_into .
         "('7z', '7z', 'archive', '7z', 'application/x-7z-compressed', '^\\\\x37\\\\x7A\\\\xBC\\\\xAF\\\\x27\\\\\\x1C', '7z')");
    $dbh->query($insert_into .
         "('hqx', 'hqx', 'archive', 'binhex', 'application/binhex', '^(This file must be converted with BinHex)', 'Binhex')");
    $dbh->query($insert_into . "('hex', 'hqx', null, null, null, null, null)");
    $dbh->query($insert_into .
         "('lzh', 'lzh', 'archive', 'lzh', 'application/x-lzh-compressed', '^.{2}\\\\x2D\\\\x6C\\\\x68', 'LZH')");
    $dbh->query($insert_into . "('lha', 'lzh', null, null, null, null, null)");
    $dbh->query($insert_into . "('zip', 'zip', 'archive', 'zip', 'application/zip', '^PK\\\\x03\\\\x04', 'Zip')");
    $dbh->query($insert_into .
         "('rar', 'rar', 'archive', 'rar', 'application/x-rar-compressed', '^Rar\\\\x21\\\\x1A\\\\x07\\\\x00', 'RAR')");
    $dbh->query($insert_into .
         "('sit', 'sit', 'archive', 'stuffit', 'application/x-stuffit', '^StuffIt \(c\)1997-|StuffIt\!|^SIT\!', 'StuffIt')");
    $dbh->query($insert_into . "('sitx', 'sit', null, null, null, null, null)");
    $dbh->query($insert_into .
         "('iso', 'iso', 'archive', 'iso', 'application/x-iso-image', '^(.{32769}|.{34817}|.{36865})CD001', 'ISO Disk Image')");
    $dbh->query($insert_into .
         "('dmg', 'dmg', 'archive', 'dmg', 'application/x-apple-diskimage', 'koly.{508}$', 'Apple Disk Image')");
    $dbh->query($insert_into .
         "('swf', 'swf', 'other', 'flash-shockwave', 'application/x-shockwave-flash', '^CWS|FWS|ZWS', 'Flash/Shockwave')");
    $dbh->query($insert_into .
         "('blorb', 'blorb', 'other', 'blorb', 'application/x-blorb', '^FORM.{4}IFRSRIdx', 'Blorb')");
    $dbh->query($insert_into . "('blb', 'blorb', null, null, null, null, null)");
    $dbh->query($insert_into . "('gblorb', 'blorb', null, null, null, null, null)");
    $dbh->query($insert_into . "('glb', 'blorb', null, null, null, null, null)");
    $dbh->query($insert_into . "('zblorb', 'blorb', null, null, null, null, null)");
    $dbh->query($insert_into . "('zlb', 'blorb', null, null, null, null, null)");
    nel_setup_stuff_done(true);
}