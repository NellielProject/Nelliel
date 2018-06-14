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
                        ('core_setting', 'nelliel', 'crypt', 'bool', 'do_password_rehash', '0'),
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
                        ('board_setting', 'nelliel', 'general', 'bool', 'file_sha256', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'file_sha512', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'graphics', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'jpeg', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'gif', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'png', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'jpeg2000', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'tiff', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'bmp', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'icon', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'photoshop', '0'),
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
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'ogg-audio', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'au', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'wma', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'midi', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'ac3', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'video', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'mpeg', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'quicktime', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'avi', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'wmv', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'mpeg4', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'matroska', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'flash-video', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'webm', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', '3gp', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'ogg-video', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'm4v', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'document', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'rich-text', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'pdf', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'msword', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'powerpoint', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'msexcel', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'plaintext', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'archive', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'gzip', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'bzip2', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'binhex', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'lzh', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'zip', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'rar', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'stuffit', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'tar', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', '7z', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'iso', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'dmg', '0'),
                        ('filetype_enable', 'nelliel', 'other', 'bool', 'other', '0'),
                        ('filetype_enable', 'nelliel', 'other', 'bool', 'flash-shockwave', '0'),
                        ('filetype_enable', 'nelliel', 'other', 'bool', 'blorb', '0')
                        ");

    nel_setup_stuff_done($result);
}

function nel_insert_filetypes()
{
    $dbh = nel_database();
    // Slash Mania!
    $result = $dbh->query("INSERT INTO " . FILETYPE_TABLE . "
    (extension, parent_extension, type, format, mime, id_regex, label)
    VALUES
    ('jpg', 'jpg', 'graphics', 'jpeg', 'image/jpeg', '^\\\\xFF\\\\xD8\\\\xFF', 'JPEG'),
    ('jpeg', 'jpg', null, null, null, null, null),
    ('jpe', 'jpg', null, null, null, null, null),
    ('gif', 'gif', 'graphics', 'gif', 'image/gif', '^(?:GIF87a|GIF89a)', 'GIF'),
    ('png', 'png', 'graphics', 'png', 'image/png', '^\\\\x89\\\\x50\\\\x4E\\\\x47\\\\x0D\\\\x0A\\\\x1A\\\\x0A', 'PNG'),
    ('jp2', 'jp2', 'graphics', 'jpeg2000', 'image/jp2', '^\\\\x00\\\\x00\\\\x00\\\\x0C\\\\x6A\\\\x50\\\\x20\\\\x20\\\\x0D\\\\x0A', 'JPEG2000'),
    ('j2k', 'jp2', null, null, null, null, null),
    ('tiff', 'tiff', 'graphics', 'tiff', 'image/tiff', '^I\\\\x20?I\\\\x2A\\\\x00|^MM\\\\x00[\\\\x2A-\\\\x2B]', 'TIFF'),
    ('tif', 'tiff', null, null, null, null, null),
    ('bmp', 'bmp', 'graphics', 'bmp', 'image/x-bmp', '^BM', 'BMP'),
    ('ico', 'ico', 'graphics', 'icon', 'image/x-icon', '^\\\\x00\\\\x00\\\\x01\\\\x00', 'Icon'),
    ('psd', 'psd', 'graphics', 'photoshop', 'image/vnd.adobe.photoshop', '^8BPS\\\\x00\\\\x01', 'PSD (Photoshop)'),
    ('tga', 'tga', 'graphics', 'tga', 'image/x-targa', '^.{1}\\\\x00', 'Truevision TGA'),
    ('pict', 'pict', 'graphics', 'pict', 'image/x-pict', '^.{522}(?:\\\\x11\\\\x01|\\\\x00\\\\x11\\\\x02\\\\xFF\\\\x0C\\\\x00)', 'PICT'),
    ('pct', 'pict', null, null, null, null, null),
    ('art', 'art', 'graphics', 'art', 'image/x-jg', '^JG[\\\\x03-\\\\x04]\\\\x0E', 'AOL ART'),
    ('cel', 'cel', 'graphics', 'cel', 'application/octet-stream', '^KiSS(?:\\\\x20\\\\x04|\\\\x20\\\\x08|\\\\x21\\\\x20|\\\\x20\\\\x20)', 'Kisekae CEL'),
    ('kcf', 'kcf', 'graphics', 'kcf', 'application/octet-stream', '^KiSS\\\\x10)', 'Kisekae Pallete'),
    ('wav', 'wav', 'audio', 'wave', 'audio/x-wave', '^RIFF.{4}WAVEfmt', 'WAVE'),
    ('aif', 'aif', 'audio', 'aiff', 'audio/aiff', '^FORM.{4}AIFF', 'AIFF'),
    ('aiff', 'aif', null, null, null, null, null),
    ('mp3', 'mp3', 'audio', 'mp3', 'audio/mpeg', '^ID3|\\\\xFF[\\\\xE0-\\\\xFF]{1}', 'MP3'),
    ('m4a', 'm4a', 'audio', 'm4a', 'audio/m4a', '^.{4}ftypM4A', 'MPEG-4 Audio'),
    ('flac', 'flac', 'audio', 'flac', 'audio/x-flac', '^fLaC\\\\x00\\\\x00\\\\x00\\\\x22', 'FLAC'),
    ('aac', 'aac', 'audio', 'aac', 'audio/aac', '^ADIF|^\\\\xFF(?:\\\\xF1|\\\\xF9)', 'AAC'),
    ('ogg', 'ogg', 'audio', 'ogg-audio', 'audio/ogg', '^OggS', 'OGG Audio'),
    ('oga', 'ogg', null, null, null, null, null),
    ('au', 'au', 'audio', 'au', 'audio/basic', '^\.snd', 'AU'),
    ('snd', 'au', null, null, null, null, null),
    ('ac3', 'ac3', 'audio', 'ac3', 'audio/ac3', '^\\\\x0B\\\\x77', 'AC3'),
    ('wma', 'wma', 'audio', 'wma', 'audio/x-ms-wma', '^\\\\x30\\\\x26\\\\xB2\\\\x75\\\\x8E\\\\x66\\\\xCF\\\\x11\\\\xA6\\\\xD9\\\\x00\\\\xAA\\\\x00\\\\x62\\\\xCE\\\\x6C', 'Windows Media Audio'),
    ('midi', 'midi', 'audio', 'midi', 'audio/midi', '^MThd', 'MIDI'),
    ('mid', 'midi', null, null, null, null, null),
    ('mpg', 'mpg', 'video', 'mpeg', 'video/mpeg', '^\\\\x00\\\\x00\\\\x01[\\\\xB0-\\\\xBF]', 'MPEG-1/MPEG-2'),
    ('mpeg', 'mpg', null, null, null, null, null),
    ('mpe', 'mpg', null, null, null, null, null),
    ('mov', 'mov', 'video', 'quicktime', 'video/quicktime', '^.{4}(?:cmov|free|ftypqt|mdat|moov|pnot|skip|wide)', 'Quicktime Movie'),
    ('avi', 'avi', 'video', 'avi', 'video/x-msvideo', '^RIFF.{4}AVI\\\\x20LIST', 'AVI'),
    ('wmv', 'wmv', 'video', 'wmv', 'video/x-ms-wmv', '^\\\\x30\\\\x26\\\\xB2\\\\x75\\\\x8E\\\\x66\\\\xCF\\\\x11\\\\xA6\\\\xD9\\\\x00\\\\xAA\\\\x00\\\\x62\\\\xCE\\\\x6C', 'Windows Media Video'),
    ('mp4', 'mp4', 'video', 'mpeg4', 'video/mp4', '^.{4}ftyp(?:iso2|isom|mp41|mp42)', 'MPEG-4 Media'),
    ('m4v', 'm4v', 'video', 'm4v', 'video/x-m4v', '^.{4}ftypmp(?:41|42|71)', 'MPEG-4 Video'),
    ('mkv', 'mkv', 'video', 'matroska', 'video/x-matroska', '^\\\\x1A\\\\x45\\\\xDF\\\\xA3', 'Matroska Media'),
    ('flv', 'flv', 'video', 'flash-video', 'video/x-flv', '^FLV\\\\x01', 'Flash Video'),
    ('webm', 'webm', 'video', 'webm', 'video/webm', '^\\\\x1A\\\\x45\\\\xDF\\\\xA3', 'WebM'),
    ('3gp', '3gp', 'video', '3gp', 'video/3gpp', '^.{4}ftyp3gp', '3GP'),
    ('ogv', 'ogv', 'video', 'ogg-video', 'video/ogg', '^OggS', 'Ogg Video'),
    ('rtf', 'rtf', 'document', 'rich-text', 'application/rtf', '^\\\\x7B\\\\x5C\\\\x72\\\\x74\\\\x66\\\\x31', 'Rich Text'),
    ('pdf', 'pdf', 'document', 'pdf', 'application/pdf', '^\\\\x25PDF', 'PDF'),
    ('doc', 'doc', 'document', 'msword', 'application/msword', '^\\\\xD0\\\\xCF\\\\x11\\\\xE0\\\\xA1\\\\xB1\\\\x1A\\\\xE1|\\\\xDB\\\\xA5\\\\x2D\\\\x00', 'Microsoft Word'),
    ('ppt', 'ppt', 'document', 'powerpoint', 'application/ms-powerpoint', '^\\\\xD0\\\\xCF\\\\x11\\\\xE0\\\\xA1\\\\xB1\\\\x1A\\\\xE1', 'PowerPoint'),
    ('xls', 'xls', 'document', 'msexcel', 'application/ms-excel', '^\\\\xD0\\\\xCF\\\\x11\\\\xE0\\\\xA1\\\\xB1\\\\x1A\\\\xE1', 'Microsoft Excel'),
    ('txt', 'txt', 'document', 'plaintext', 'text/plain', '', 'Plaintext'),
    ('gz', 'gz', 'archive', 'gzip', 'application/gzip', '^\\\\x1F\\\\x8B\\\\x08', 'GZip'),
    ('tgz', 'gz', null, null, null, null, null),
    ('gzip', 'gz', null, null, null, null, null),
    ('bz2', 'bz2', 'archive', 'bzip2', 'application/x-bzip2', '^BZh.{1}\\\\x31\\\\x41\\\\x59\\\\x26\\\\x53\\\\x59', 'bzip2'),
    ('tbz2', 'bz2', null, null, null, null, null),
    ('tbz', 'bz2', null, null, null, null, null),
    ('tar', 'tar', 'archive', 'tar', 'application/x-tar', '^.{257}ustar', 'TAR'),
    ('7z', '7z', 'archive', '7z', 'application/x-7z-compressed', '^\\\\x37\\\\x7A\\\\xBC\\\\xAF\\\\x27\\\\\\x1C', '7z'),
    ('hqx', 'hqx', 'archive', 'binhex', 'application/binhex', '^(This file must be converted with BinHex)', 'Binhex'),
    ('hex', 'hqx', null, null, null, null, null),
    ('lzh', 'lzh', 'archive', 'lzh', 'application/x-lzh-compressed', '^.{2}\\\\x2D\\\\x6C\\\\x68', 'LZH'),
    ('lha', 'lzh', null, null, null, null, null),
    ('zip', 'zip', 'archive', 'zip', 'application/zip', '^PK\\\\x03\\\\x04', 'Zip'),
    ('rar', 'rar', 'archive', 'rar', 'application/x-rar-compressed', '^Rar\\\\x21\\\\x1A\\\\x07\\\\x00', 'RAR'),
    ('sit', 'sit', 'archive', 'stuffit', 'application/x-stuffit', '^StuffIt \(c\)1997-|StuffIt\!|^SIT\!', 'StuffIt'),
    ('sitx', 'sit', null, null, null, null, null),
    ('iso', 'iso', 'archive', 'iso', 'application/x-iso-image', '^(.{32769}|.{34817}|.{36865})CD001', 'ISO Disk Image'),
    ('dmg', 'dmg', 'archive', 'dmg', 'application/x-apple-diskimage', 'koly.{508}$', 'Apple Disk Image'),
    ('swf', 'swf', 'other', 'flash-shockwave', 'application/x-shockwave-flash', '^CWS|FWS|ZWS', 'Flash/Shockwave'),
    ('blorb', 'blorb', 'other', 'blorb', 'application/x-blorb', '^FORM.{4}IFRSRIdx', 'Blorb'),
    ('blb', 'blorb', null, null, null, null, null),
    ('gblorb', 'blorb', null, null, null, null, null),
    ('glb', 'blorb', null, null, null, null, null),
    ('zblorb', 'blorb', null, null, null, null, null),
    ('zlb', 'blorb', null, null, null, null, null)");

    nel_setup_stuff_done($result);
}