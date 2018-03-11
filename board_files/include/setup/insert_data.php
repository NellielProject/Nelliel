<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_insert_site_config_defaults()
{
    $dbh = nel_database();
    $result = $dbh->query("INSERT INTO " . SITE_CONFIG_TABLE . " (config_type, config_owner, config_category, data_type, config_name, setting)
                VALUES  ('core_setting', 'nelliel', 'general', 'str', 'home_page', '../'),
                        ('core_setting', 'nelliel', 'crypt', 'str', 'post_password_algorithm', 'sha256'),
                        ('core_setting', 'nelliel', 'crypt', 'str', 'secure_tripcode_algorithm', 'sha256'),
                        ('core_setting', 'nelliel', 'crypt', 'bool', 'use_password_default_algorithm', '1'),
                        ('core_setting', 'nelliel', 'crypt', 'bool', 'do_password_rehash', '0'),
                        ('core_setting', 'nelliel', 'crypt', 'bool', 'do_sha2_fallback', '1'),
                        ('schema_version', 'nelliel', 'database', 'str', 'original_bans_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'current_bans_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'original_user_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'current_user_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'original_roles_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'current_roles_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'original_user_role_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'current_user_role_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'original_permissions_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'current_permissions_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'original_logins_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'current_logins_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'original_board_data_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'current_board_data_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'original_site_config_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'current_site_config_schema', '001')
                        ");

    nel_setup_stuff_done($result);
}

function nel_insert_role_defaults()
{
    $dbh = nel_database();
    $result = $dbh->query("INSERT INTO " . ROLES_TABLE . "
    (role_id, role_level, role_title, capcode_text)
    VALUES
    ('SUPER_ADMIN', 1000, 'Site Administrator', '## Site Administrator ##'),
    ('BOARD_ADMIN', 100, 'Board Administrator', '## Board Administrator ##'),
    ('MOD', 50, 'Moderator', '## Moderator ##'),
    ('JANITOR', 10, 'Janitor', '## Janitor ##')");

    nel_setup_stuff_done($result);
}

function nel_insert_permissions_defaults()
{
    $dbh = nel_database();
    $result = $dbh->query("INSERT INTO " . PERMISSIONS_TABLE . " (role_id, perm_id, perm_setting)
                        VALUES  ('SUPER_ADMIN', 'perm_config_access', 1),
                                ('SUPER_ADMIN', 'perm_config_modify', 1),
                                ('SUPER_ADMIN', 'perm_user_access', 1),
                                ('SUPER_ADMIN', 'perm_user_add', 1),
                                ('SUPER_ADMIN', 'perm_user_modify', 1),
                                ('SUPER_ADMIN', 'perm_user_delete', 1),
                                ('SUPER_ADMIN', 'perm_user_change_pass', 1),
                                ('SUPER_ADMIN', 'perm_role_access', 1),
                                ('SUPER_ADMIN', 'perm_role_add', 1),
                                ('SUPER_ADMIN', 'perm_role_modify', 1),
                                ('SUPER_ADMIN', 'perm_role_delete', 1),
                                ('SUPER_ADMIN', 'perm_ban_access', 1),
                                ('SUPER_ADMIN', 'perm_ban_add', 1),
                                ('SUPER_ADMIN', 'perm_ban_modify', 1),
                                ('SUPER_ADMIN', 'perm_ban_delete', 1),
                                ('SUPER_ADMIN', 'perm_post_access', 1),
                                ('SUPER_ADMIN', 'perm_post_modify', 1),
                                ('SUPER_ADMIN', 'perm_post_delete', 1),
                                ('SUPER_ADMIN', 'perm_post_file_delete', 1),
                                ('SUPER_ADMIN', 'perm_post_default_name', 1),
                                ('SUPER_ADMIN', 'perm_post_custom_name', 1),
                                ('SUPER_ADMIN', 'perm_post_override_anon', 1),
                                ('SUPER_ADMIN', 'perm_post_sticky', 1),
                                ('SUPER_ADMIN', 'perm_post_unsticky', 1),
                                ('SUPER_ADMIN', 'perm_post_lock', 1),
                                ('SUPER_ADMIN', 'perm_post_unlock', 1),
                                ('SUPER_ADMIN', 'perm_post_in_locked', 1),
                                ('SUPER_ADMIN', 'perm_post_comment', 1),
                                ('SUPER_ADMIN', 'perm_post_permsage', 1),
                                ('SUPER_ADMIN', 'perm_regen_caches', 1),
                                ('SUPER_ADMIN', 'perm_regen_index', 1),
                                ('SUPER_ADMIN', 'perm_regen_threads', 1),
                                ('SUPER_ADMIN', 'perm_modmode_access', 1),
                                ('SUPER_ADMIN', 'perm_modmode_view_ips', 1),
                                ('BOARD_ADMIN', 'perm_config_access', 1),
                                ('BOARD_ADMIN', 'perm_config_modify', 1),
                                ('BOARD_ADMIN', 'perm_user_access', 1),
                                ('BOARD_ADMIN', 'perm_user_add', 1),
                                ('BOARD_ADMIN', 'perm_user_modify', 1),
                                ('BOARD_ADMIN', 'perm_user_delete', 1),
                                ('BOARD_ADMIN', 'perm_user_change_pass', 1),
                                ('BOARD_ADMIN', 'perm_role_access', 1),
                                ('BOARD_ADMIN', 'perm_role_add', 1),
                                ('BOARD_ADMIN', 'perm_role_modify', 1),
                                ('BOARD_ADMIN', 'perm_role_delete', 1),
                                ('BOARD_ADMIN', 'perm_ban_access', 1),
                                ('BOARD_ADMIN', 'perm_ban_add', 1),
                                ('BOARD_ADMIN', 'perm_ban_modify', 1),
                                ('BOARD_ADMIN', 'perm_ban_delete', 1),
                                ('BOARD_ADMIN', 'perm_post_access', 1),
                                ('BOARD_ADMIN', 'perm_post_modify', 1),
                                ('BOARD_ADMIN', 'perm_post_delete', 1),
                                ('BOARD_ADMIN', 'perm_post_file_delete', 1),
                                ('BOARD_ADMIN', 'perm_post_default_name', 1),
                                ('BOARD_ADMIN', 'perm_post_custom_name', 1),
                                ('BOARD_ADMIN', 'perm_post_override_anon', 1),
                                ('BOARD_ADMIN', 'perm_post_sticky', 1),
                                ('BOARD_ADMIN', 'perm_post_unsticky', 1),
                                ('BOARD_ADMIN', 'perm_post_lock', 1),
                                ('BOARD_ADMIN', 'perm_post_unlock', 1),
                                ('BOARD_ADMIN', 'perm_post_in_locked', 1),
                                ('BOARD_ADMIN', 'perm_post_comment', 1),
                                ('BOARD_ADMIN', 'perm_post_permsage', 1),
                                ('BOARD_ADMIN', 'perm_regen_caches', 1),
                                ('BOARD_ADMIN', 'perm_regen_index', 1),
                                ('BOARD_ADMIN', 'perm_regen_threads', 1),
                                ('BOARD_ADMIN', 'perm_modmode_access', 1),
                                ('BOARD_ADMIN', 'perm_modmode_view_ips', 1),
                                ('MOD', 'perm_config_access', 0),
                                ('MOD', 'perm_config_change', 0),
                                ('MOD', 'perm_user_access', 0),
                                ('MOD', 'perm_user_add', 0),
                                ('MOD', 'perm_user_modify', 0),
                                ('MOD', 'perm_user_delete', 0),
                                ('MOD', 'perm_user_change_pass', 1),
                                ('MOD', 'perm_role_access', 0),
                                ('MOD', 'perm_role_add', 0),
                                ('MOD', 'perm_role_modify', 0),
                                ('MOD', 'perm_role_delete', 0),
                                ('MOD', 'perm_ban_access', 1),
                                ('MOD', 'perm_ban_add', 1),
                                ('MOD', 'perm_ban_modify', 1),
                                ('MOD', 'perm_ban_delete', 1),
                                ('MOD', 'perm_post_access', 1),
                                ('MOD', 'perm_post_modify', 0),
                                ('MOD', 'perm_post_delete', 1),
                                ('MOD', 'perm_post_file_delete', 1),
                                ('MOD', 'perm_post_default_name', 1),
                                ('MOD', 'perm_post_custom_name', 0),
                                ('MOD', 'perm_post_override_anon', 0),
                                ('MOD', 'perm_post_sticky', 1),
                                ('MOD', 'perm_post_unsticky', 1),
                                ('MOD', 'perm_post_lock', 1),
                                ('MOD', 'perm_post_unlock', 1),
                                ('MOD', 'perm_post_in_locked', 1),
                                ('MOD', 'perm_post_comment', 1),
                                ('MOD', 'perm_post_permsage', 1),
                                ('MOD', 'perm_regen_caches', 0),
                                ('MOD', 'perm_regen_index', 0),
                                ('MOD', 'perm_regen_threads', 0),
                                ('MOD', 'perm_modmode_access', 1),
                                ('MOD', 'perm_modmode_view_ips', 1),
                                ('JANITOR', 'perm_config_access', 0),
                                ('JANITOR', 'perm_config_change', 0),
                                ('JANITOR', 'perm_user_access', 0),
                                ('JANITOR', 'perm_user_add', 0),
                                ('JANITOR', 'perm_user_modify', 0),
                                ('JANITOR', 'perm_user_delete', 0),
                                ('JANITOR', 'perm_user_change_pass', 1),
                                ('JANITOR', 'perm_role_access', 0),
                                ('JANITOR', 'perm_role_add', 0),
                                ('JANITOR', 'perm_role_modify', 0),
                                ('JANITOR', 'perm_role_delete', 0),
                                ('JANITOR', 'perm_ban_access', 0),
                                ('JANITOR', 'perm_ban_add', 0),
                                ('JANITOR', 'perm_ban_modify', 0),
                                ('JANITOR', 'perm_ban_delete', 0),
                                ('JANITOR', 'perm_post_access', 1),
                                ('JANITOR', 'perm_post_modify', 0),
                                ('JANITOR', 'perm_post_delete', 1),
                                ('JANITOR', 'perm_post_file_delete', 1),
                                ('JANITOR', 'perm_post_default_name', 0),
                                ('JANITOR', 'perm_post_custom_name', 0),
                                ('JANITOR', 'perm_post_override_anon', 0),
                                ('JANITOR', 'perm_post_sticky', 0),
                                ('JANITOR', 'perm_post_unsticky', 0),
                                ('JANITOR', 'perm_post_lock', 0),
                                ('JANITOR', 'perm_post_unlock', 0),
                                ('JANITOR', 'perm_post_in_locked', 0),
                                ('JANITOR', 'perm_post_comment', 0),
                                ('JANITOR', 'perm_post_permsage', 0),
                                ('JANITOR', 'perm_regen_caches', 0),
                                ('JANITOR', 'perm_regen_index', 0),
                                ('JANITOR', 'perm_regen_threads', 0),
                                ('JANITOR', 'perm_modmode_access', 1),
                                ('JANITOR', 'perm_modmode_view_ips', 0)");

    nel_setup_stuff_done($result);
}

function nel_insert_default_admin()
{
    if (DEFAULTADMIN === '' || DEFAULTADMIN_PASS === '')
    {
        return false;
    }

    nel_verify_hash_algorithm();

    $dbh = nel_database();
    $result = $dbh->query('SELECT 1 FROM "' . USER_TABLE . '" WHERE "user_id" = \'' . DEFAULTADMIN . '\'');

    if ($result->fetch() !== false)
    {
        return false;
    }

    $result = $dbh->query('INSERT INTO "' . USER_TABLE . '" (user_id, user_password, active, failed_logins, last_failed_login)
    VALUES (\'' . DEFAULTADMIN . '\', \'' . nel_password_hash(DEFAULTADMIN_PASS, NELLIEL_PASS_ALGORITHM) .
    '\', 1, 1, 0)');

    nel_setup_stuff_done($result);
}

function nel_insert_default_admin_role()
{
    if (DEFAULTADMIN === '' || DEFAULTADMIN_PASS === '')
    {
        return false;
    }

    $dbh = nel_database();
    $result = $dbh->query('SELECT 1 FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = \'' . DEFAULTADMIN . '\'');

    if ($result->fetch() !== false)
    {
        return false;
    }

    $result = $dbh->query('INSERT INTO "' . USER_ROLE_TABLE . '" (user_id, role_id, all_boards)
    VALUES
    (\'' . DEFAULTADMIN . '\', \'SUPER_ADMIN\', 1)');

    nel_setup_stuff_done($result);
}

function nel_insert_board_config_defaults($config_table)
{
    $dbh = nel_database();
    $result = $dbh->query("INSERT INTO " . $config_table . " (config_type, config_owner, config_category, data_type, config_name, setting)
                VALUES  ('schema_version', 'nelliel', 'database', 'str', 'original_posts_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'current_posts_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'original_threads_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'current_threads_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'original_files_schema', '001'),
                        ('schema_version', 'nelliel', 'database', 'str', 'current_files_schema', '001'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'allow_tripkeys', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'force_anonymous', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'show_title', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'show_favicon', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'show_logo', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_thumb', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_magick', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_file_icon', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_png_thumb', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'animated_gif_preview', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'require_image_start', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'require_image_always', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'allow_multifile', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'allow_op_multifile', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_new_imgdel', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_fgsfds', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_spambot_trap', '1'),
                        ('board_setting', 'nelliel', 'general', 'str', 'board_name', 'Nelliel-powered image board'),
                        ('board_setting', 'nelliel', 'general', 'str', 'board_favicon', ''),
                        ('board_setting', 'nelliel', 'general', 'str', 'board_logo', ''),
                        ('board_setting', 'nelliel', 'general', 'int', 'board_language', 'en-us'),
                        ('board_setting', 'nelliel', 'general', 'int', 'thread_delay', '60'),
                        ('board_setting', 'nelliel', 'general', 'int', 'reply_delay', '15'),
                        ('board_setting', 'nelliel', 'general', 'int', 'abbreviate_thread', '5'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_post_files', '3'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_files_row', '3'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_multi_width', '175'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_multi_height', '175'),
                        ('board_setting', 'nelliel', 'general', 'int', 'jpeg_quality', '85'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_width', '256'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_height', '256'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_filesize', '1024'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_name_length', '100'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_email_length', '100'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_subject_length', '100'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_comment_length', '5000'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_comment_lines', '60'),
                        ('board_setting', 'nelliel', 'general', 'int', 'comment_display_lines', '15'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_source_length', '255'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_license_length', '255'),
                        ('board_setting', 'nelliel', 'general', 'int', 'threads_per_page', '10'),
                        ('board_setting', 'nelliel', 'general', 'int', 'page_limit', '10'),
                        ('board_setting', 'nelliel', 'general', 'int', 'page_buffer', '0'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_posts', '1000'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_bumps', '750'),
                        ('board_setting', 'nelliel', 'general', 'str', 'tripkey_marker', '!'),
                        ('board_setting', 'nelliel', 'general', 'str', 'date_format', 'ISO'),
                        ('board_setting', 'nelliel', 'general', 'str', 'old_threads', 'ARCHIVE'),
                        ('board_setting', 'nelliel', 'general', 'str', 'date_separator', '/'),
                        ('board_setting', 'nelliel', 'general', 'str', 'fgsfds_name', 'FGSFDS'),
                        ('board_setting', 'nelliel', 'general', 'str', 'indent_marker', '>>'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'file_sha256', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'graphics', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'jpeg', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'gif', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'png', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'jpeg2000', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'tiff', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'bmp', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'ico', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'psd', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'tga', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'pict', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'art', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'cel', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'kcf', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'audio', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'wave', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'aiff', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'mp3', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'm4a', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'flac', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'aac', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'ogg', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'au', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'wma', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'midi', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'ac3', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'video', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'mpeg', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'mov', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'avi', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'wmv', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'mp4', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'mkv', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'flv', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'webm', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', '3gp', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'ogv', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'document', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'rtf', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'pdf', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'doc', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'ppt', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'xls', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'text', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'archive', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'gzip', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'bzip2', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'binhex', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'lzh', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'zip', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'rar', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'sit', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'tar', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', '7z', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'iso', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'dmg', '0'),
                        ('filetype_enable', 'nelliel', 'other', 'bool', 'other', '0'),
                        ('filetype_enable', 'nelliel', 'other', 'bool', 'swf', '0'),
                        ('filetype_enable', 'nelliel', 'other', 'bool', 'blorb', '0')
                        ");

    nel_setup_stuff_done($result);
}
