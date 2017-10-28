<?php

//
// Because auto increment isn't really standard and everyone has to be a bit different
// Pass $int_column as SMALLINT, INTEGER or BIGINT. This is basically just to deal with Postgres
//
function nel_autoincrement_column($int_column)
{
    $auto = '';

    if (SQLTYPE === 'MYSQL')
    {
        $auto = 'AUTO_INCREMENT';
    }
    else if (SQLTYPE === 'POSTGRES')
    {
        if ($int_column === 'SMALLINT')
        {
            $int_column = 'SMALLSERIAL';
        }

        if ($int_column === 'INTEGER')
        {
            $int_column = 'SERIAL';
        }

        if ($int_column === 'BIGINT')
        {
            $int_column = 'BIGSERIAL';
        }
    }
    else if (SQLTYPE === 'SQLITE')
    {
        $auto = 'AUTOINCREMENT';
    }

    return array($int_column, $auto);
}

function nel_table_options()
{
    $options = '';

    if (SQLTYPE === 'MYSQL')
    {
        $options = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci';

        if (nel_check_for_innodb())
        {
            $options .= ' ENGINE = InnoDB';
        }
    }

    return $options . ';';
}

function nel_check_for_innodb()
{
    $dbh = nel_database();
    $result = $dbh->query("SHOW ENGINES");
    $list = $result->fetchAll(PDO::FETCH_ASSOC);

    foreach ($list as $entry)
    {
        if ($entry['Engine'] === 'InnoDB' && ($entry['Support'] === 'DEFAULT' || $entry['Support'] === 'YES'))
        {
            return true;
        }
    }

    return false;
}

function nel_create_table_query($schema, $table_name)
{
    $dbh = nel_database();

    if ($dbh->tableExists($table_name))
    {
        return false;
    }

    $result = $dbh->query($schema);

    if (!$result)
    {
        print_r($dbh->errorInfo());
        $dbh->tableFail($table_name);
    }

    return $result;
}

function nel_create_posts_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "post_number"       ' . $auto_inc[0] . ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "parent_thread"     INTEGER NOT NULL DEFAULT 0,
        "poster_name"       VARCHAR(255) DEFAULT NULL,
        "post_password"     VARCHAR(255) DEFAULT NULL,
        "tripcode"          VARCHAR(255) DEFAULT NULL,
        "secure_tripcode"   VARCHAR(255) DEFAULT NULL,
        "email"             VARCHAR(255) DEFAULT NULL,
        "subject"           VARCHAR(255) DEFAULT NULL,
        "comment"           TEXT,
        "ip_address"        VARCHAR(45) DEFAULT NULL,
        "post_time"         BIGINT NOT NULL DEFAULT 0,
        "has_file"          SMALLINT NOT NULL DEFAULT 0,
        "file_count"        SMALLINT NOT NULL DEFAULT 0,
        "external_content"  SMALLINT NOT NULL DEFAULT 0,
        "external_count"    SMALLINT NOT NULL DEFAULT 0,
        "license"           VARCHAR(255) DEFAULT NULL,
        "op"                SMALLINT NOT NULL DEFAULT 0,
        "sage"              SMALLINT NOT NULL DEFAULT 0,
        "mod_post"          VARCHAR(255) DEFAULT NULL,
        "mod_comment"       VARCHAR(255) DEFAULT NULL,
        "post_hash"         CHAR(40) DEFAULT NULL
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);
    nel_setup_stuff_done($result);
}

function nel_create_threads_table($table_name)
{
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "thread_id"         INTEGER NOT NULL PRIMARY KEY,
        "first_post"        INTEGER NOT NULL DEFAULT 0,
        "last_post"         INTEGER NOT NULL DEFAULT 0,
        "second_last_post"  INTEGER NOT NULL DEFAULT 0,
        "last_bump_time"    BIGINT NOT NULL DEFAULT 0,
        "total_files"       INTEGER NOT NULL DEFAULT 0,
        "total_external"    INTEGER NOT NULL DEFAULT 0,
        "last_update"       BIGINT NOT NULL DEFAULT 0,
        "post_count"        INTEGER NOT NULL DEFAULT 0,
        "thread_sage"       SMALLINT NOT NULL DEFAULT 0,
        "sticky"            SMALLINT NOT NULL DEFAULT 0,
        "archive_status"    SMALLINT NOT NULL DEFAULT 0,
        "locked"            SMALLINT NOT NULL DEFAULT 0
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);
    nel_setup_stuff_done($result);
}

function nel_create_files_table($table_name)
{
    $dbh = nel_database();
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "entry"            ' . $auto_inc[0] . ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "parent_thread"     INTEGER NOT NULL DEFAULT 0,
        "post_ref"          INTEGER NOT NULL DEFAULT 0,
        "file_order"        SMALLINT NOT NULL DEFAULT 1,
        "supertype"         VARCHAR(255) DEFAULT NULL,
        "subtype"           VARCHAR(255) DEFAULT NULL,
        "mime"              VARCHAR(255) DEFAULT NULL,
        "filename"          VARCHAR(255) DEFAULT NULL,
        "extension"         VARCHAR(255) DEFAULT NULL,
        "image_width"       INTEGER DEFAULT NULL,
        "image_height"      INTEGER DEFAULT NULL,
        "preview_name"      VARCHAR(255) DEFAULT NULL,
        "preview_width"     SMALLINT DEFAULT NULL,
        "preview_height"    SMALLINT DEFAULT NULL,
        "filesize"          INTEGER DEFAULT 0,
        "md5"               CHAR(32) DEFAULT NULL,
        "sha1"              CHAR(40) DEFAULT NULL,
        "sha256"            CHAR(64) DEFAULT NULL,
        "source"            VARCHAR(255) DEFAULT NULL,
        "license"           VARCHAR(255) DEFAULT NULL,
        "exif"              TEXT DEFAULT NULL,
        "extra_meta"        TEXT DEFAULT NULL
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);

    if ($result)
    {
        $dbh->query('CREATE INDEX index_md5 ON ' . $table_name . ' (md5);');
        $dbh->query('CREATE INDEX index_sha1 ON ' . $table_name . ' (sha1);');
        $dbh->query('CREATE INDEX index_sha256 ON ' . $table_name . ' (sha256);');
    }

    nel_setup_stuff_done($result);
}

function nel_create_external_table($table_name)
{
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "entry"            ' . $auto_inc[0] . ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "parent_thread"     INTEGER NOT NULL DEFAULT 0,
        "post_ref"          INTEGER NOT NULL DEFAULT 0,
        "content_order"     SMALLINT NOT NULL DEFAULT 1,
        "content_type"      VARCHAR(255) DEFAULT NULL,
        "content_url"       VARCHAR(2048) DEFAULT NULL,
        "source"            VARCHAR(255) DEFAULT NULL,
        "license"           VARCHAR(255) DEFAULT NULL
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);
    nel_setup_stuff_done($result);
}

function nel_create_bans_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "ban_id"            ' . $auto_inc[0] . ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "type"              VARCHAR(255) DEFAULT NULL,
        "ip_address"        VARCHAR(45) DEFAULT NULL,
        "name"              VARCHAR(255) DEFAULT NULL,
        "reason"            TEXT DEFAULT NULL,
        "length"            BIGINT NOT NULL DEFAULT 0,
        "ban_time"          BIGINT NOT NULL DEFAULT 0,
        "appeal"            TEXT DEFAULT NULL,
        "appeal_response"   TEXT DEFAULT NULL,
        "appeal_status"     SMALLINT NOT NULL DEFAULT 0
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);
    nel_setup_stuff_done($result);
}

function nel_create_config_table($table_name)
{
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "config_name"       VARCHAR(255) DEFAULT NULL UNIQUE,
        "config_type"       VARCHAR(255) DEFAULT NULL,
        "data_type"         VARCHAR(255) DEFAULT NULL,
        "setting"           VARCHAR(255) DEFAULT NULL
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);

    if ($result !== false)
    {
        nel_insert_config_defaults();
    }

    nel_setup_stuff_done($result);
}

function nel_create_user_table($table_name)
{
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "user_id"           VARCHAR(255) DEFAULT NULL UNIQUE,
        "user_title"        VARCHAR(255) DEFAULT NULL,
        "user_password"     VARCHAR(255) DEFAULT NULL,
        "user_tripcode"     VARCHAR(255) DEFAULT NULL,
        "role_id"           VARCHAR(255) DEFAULT NULL,
        "active"            SMALLINT NOT NULL DEFAULT 0,
        "failed_logins"     SMALLINT NOT NULL DEFAULT 0,
        "last_failed_login" BIGINT NOT NULL DEFAULT 0
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);

    if ($result !== false)
    {
        nel_insert_default_admin();
    }

    nel_setup_stuff_done($result);
}

function nel_create_roles_table($table_name)
{
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "role_id"               VARCHAR(255) DEFAULT NULL UNIQUE,
        "role_level"            SMALLINT NOT NULL DEFAULT 0,
        "role_title"            VARCHAR(255) DEFAULT NULL,
        "capcode_text"          VARCHAR(255) DEFAULT NULL
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);

    if ($result !== false)
    {
        nel_insert_role_defaults();
    }

    nel_setup_stuff_done($result);
}

function nel_create_permissions_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "entry"                ' . $auto_inc[0] . ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "role_id"               VARCHAR(255) DEFAULT NULL,
        "perm_id"               VARCHAR(255) DEFAULT NULL,
        "perm_setting"          SMALLINT NOT NULL DEFAULT 0
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);

    if ($result !== false)
    {
        nel_insert_permissions_defaults();
    }

    nel_setup_stuff_done($result);
}

function nel_insert_role_defaults()
{
    $dbh = nel_database();
    $result = $dbh->query("SELECT 1 FROM " . ROLES_TABLE . " WHERE role_id='ADMIN'");

    if ($result->fetch() !== false)
    {
        return false;
    }

    $result = $dbh->query("INSERT INTO " . ROLES_TABLE . "
    (role_id, role_level, role_title, capcode_text)
    VALUES
    ('ADMIN', 100, 'Administrator', '## Administrator ##')
    ('MOD', 50, 'Moderator', '## Moderator ##')
    ('JANITOR', 10, 'Janitor', '## Janitor ##')");

    nel_setup_stuff_done($result);
}

function nel_insert_permissions_defaults()
{
    $dbh = nel_database();
    $result = $dbh->query("INSERT INTO " . PERMISSIONS_TABLE . " (role_id, perm_id, perm_setting)
                        VALUES  ('ADMIN', 'perm_config_access', 1),
                                ('ADMIN', 'perm_config_change', 1),
                                ('ADMIN', 'perm_user_access', 1),
                                ('ADMIN', 'perm_user_add', 1),
                                ('ADMIN', 'perm_user_modify', 1),
                                ('ADMIN', 'perm_user_delete', 1),
                                ('ADMIN', 'perm_user_change_pass', 1),
                                ('ADMIN', 'perm_role_access', 1),
                                ('ADMIN', 'perm_role_add', 1),
                                ('ADMIN', 'perm_role_modify', 1),
                                ('ADMIN', 'perm_role_delete', 1),
                                ('ADMIN', 'perm_ban_access', 1),
                                ('ADMIN', 'perm_ban_add', 1),
                                ('ADMIN', 'perm_ban_modify', 1),
                                ('ADMIN', 'perm_ban_delete', 1),
                                ('ADMIN', 'perm_post_access', 1),
                                ('ADMIN', 'perm_post_modify', 1),
                                ('ADMIN', 'perm_post_delete', 1),
                                ('ADMIN', 'perm_post_file_delete', 1),
                                ('ADMIN', 'perm_post_default_name', 1),
                                ('ADMIN', 'perm_post_custom_name', 1),
                                ('ADMIN', 'perm_post_override_anon', 1),
                                ('ADMIN', 'perm_post_sticky', 1),
                                ('ADMIN', 'perm_post_unsticky', 1),
                                ('ADMIN', 'perm_post_lock', 1),
                                ('ADMIN', 'perm_post_unlock', 1),
                                ('ADMIN', 'perm_post_in_locked', 1),
                                ('ADMIN', 'perm_post_comment', 1),
                                ('ADMIN', 'perm_post_permsage', 1),
                                ('ADMIN', 'perm_regen_caches', 1),
                                ('ADMIN', 'perm_regen_index', 1),
                                ('ADMIN', 'perm_regen_threads', 1),
                                ('ADMIN', 'perm_modmode_access', 1),
                                ('ADMIN', 'perm_modmode_view_ips', 1),
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
    $result = $dbh->query("SELECT 1 FROM " . USER_TABLE . " WHERE role_id='ADMIN'");

    if ($result->fetch() !== false)
    {
        return false;
    }

    $result = $dbh->query("INSERT INTO " . USER_TABLE . " (user_id, user_password, role_id, active, failed_logins, last_failed_login)
    VALUES
    ('" . DEFAULTADMIN .
         "', '" . nel_password_hash(DEFAULTADMIN_PASS, NELLIEL_PASS_ALGORITHM) . "',
    'ADMIN', 1, 1, 0)");

    nel_setup_stuff_done($result);
}

function nel_insert_config_defaults()
{
    $dbh = nel_database();
    $result = $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting)
                VALUES  ('technical', 'str', 'original_schema_version', '002'),
                        ('technical', 'str', 'current_schema_version', '002'),
                        ('board_setting', 'bool', 'allow_tripkeys', '1'),
                        ('board_setting', 'bool', 'force_anonymous', ''),
                        ('board_setting', 'bool', 'show_title', '1'),
                        ('board_setting', 'bool', 'show_favicon', ''),
                        ('board_setting', 'bool', 'show_logo', ''),
                        ('board_setting', 'bool', 'use_thumb', '1'),
                        ('board_setting', 'bool', 'use_magick', ''),
                        ('board_setting', 'bool', 'use_file_icon', '1'),
                        ('board_setting', 'bool', 'use_png_thumb', ''),
                        ('board_setting', 'bool', 'require_image_start', '1'),
                        ('board_setting', 'bool', 'require_image_always', ''),
                        ('board_setting', 'bool', 'allow_multifile', ''),
                        ('board_setting', 'bool', 'allow_op_multifile', ''),
                        ('board_setting', 'bool', 'use_new_imgdel', '1'),
                        ('board_setting', 'bool', 'use_fgsfds', '1'),
                        ('board_setting', 'bool', 'use_spambot_trap', '1'),
                        ('board_setting', 'str', 'board_name', 'Nelliel-powered image board'),
                        ('board_setting', 'str', 'board_favicon', ''),
                        ('board_setting', 'str', 'board_logo', ''),
                        ('board_setting', 'int', 'thread_delay', '60'),
                        ('board_setting', 'int', 'reply_delay', '15'),
                        ('board_setting', 'int', 'abbreviate_thread', '5'),
                        ('board_setting', 'int', 'max_post_files', '3'),
                        ('board_setting', 'int', 'max_files_row', '3'),
                        ('board_setting', 'int', 'max_multi_width', '175'),
                        ('board_setting', 'int', 'max_multi_height', '175'),
                        ('board_setting', 'int', 'jpeg_quality', '85'),
                        ('board_setting', 'int', 'max_width', '256'),
                        ('board_setting', 'int', 'max_height', '256'),
                        ('board_setting', 'int', 'max_filesize', '1024'),
                        ('board_setting', 'int', 'max_name_length', '100'),
                        ('board_setting', 'int', 'max_email_length', '100'),
                        ('board_setting', 'int', 'max_subject_length', '100'),
                        ('board_setting', 'int', 'max_comment_length', '1000'),
                        ('board_setting', 'int', 'max_comment_lines', '25'),
                        ('board_setting', 'int', 'max_source_length', '250'),
                        ('board_setting', 'int', 'max_license_length', '100'),
                        ('board_setting', 'int', 'threads_per_page', '10'),
                        ('board_setting', 'int', 'page_limit', '10'),
                        ('board_setting', 'int', 'page_buffer', '5'),
                        ('board_setting', 'int', 'max_posts', '1000'),
                        ('board_setting', 'int', 'max_bumps', '750'),
                        ('board_setting', 'str', 'tripkey_marker', '!'),
                        ('board_setting', 'str', 'date_format', 'ISO'),
                        ('board_setting', 'str', 'old_threads', 'ARCHIVE'),
                        ('board_setting', 'str', 'date_separator', '/'),
                        ('board_setting', 'str', 'fgsfds_name', 'FGSFDS'),
                        ('filetype_enable', 'bool', 'g_graphics', '1'),
                        ('filetype_enable', 'bool', 'g_jpeg', '1'),
                        ('filetype_enable', 'bool', 'g_gif', '1'),
                        ('filetype_enable', 'bool', 'g_png', '1'),
                        ('filetype_enable', 'bool', 'g_jpeg2000', '1'),
                        ('filetype_enable', 'bool', 'g_tiff', ''),
                        ('filetype_enable', 'bool', 'g_bmp', '1'),
                        ('filetype_enable', 'bool', 'g_ico', ''),
                        ('filetype_enable', 'bool', 'g_psd', ''),
                        ('filetype_enable', 'bool', 'g_tga', ''),
                        ('filetype_enable', 'bool', 'g_pict', ''),
                        ('filetype_enable', 'bool', 'g_art', ''),
                        ('filetype_enable', 'bool', 'a_audio', ''),
                        ('filetype_enable', 'bool', 'a_wav', ''),
                        ('filetype_enable', 'bool', 'a_aiff', ''),
                        ('filetype_enable', 'bool', 'a_mp3', ''),
                        ('filetype_enable', 'bool', 'a_m4a', ''),
                        ('filetype_enable', 'bool', 'a_flac', ''),
                        ('filetype_enable', 'bool', 'a_aac', ''),
                        ('filetype_enable', 'bool', 'a_ogg', ''),
                        ('filetype_enable', 'bool', 'a_au', ''),
                        ('filetype_enable', 'bool', 'a_wma', ''),
                        ('filetype_enable', 'bool', 'a_midi', ''),
                        ('filetype_enable', 'bool', 'a_ac3', ''),
                        ('filetype_enable', 'bool', 'v_video', ''),
                        ('filetype_enable', 'bool', 'v_mpeg', ''),
                        ('filetype_enable', 'bool', 'v_mov', ''),
                        ('filetype_enable', 'bool', 'v_avi', ''),
                        ('filetype_enable', 'bool', 'v_wmv', ''),
                        ('filetype_enable', 'bool', 'v_mp4', ''),
                        ('filetype_enable', 'bool', 'v_mkv', ''),
                        ('filetype_enable', 'bool', 'v_flv', ''),
                        ('filetype_enable', 'bool', 'd_document', ''),
                        ('filetype_enable', 'bool', 'd_rtf', ''),
                        ('filetype_enable', 'bool', 'd_pdf', ''),
                        ('filetype_enable', 'bool', 'd_doc', ''),
                        ('filetype_enable', 'bool', 'd_ppt', ''),
                        ('filetype_enable', 'bool', 'd_xls', ''),
                        ('filetype_enable', 'bool', 'd_txt', ''),
                        ('filetype_enable', 'bool', 'r_archive', ''),
                        ('filetype_enable', 'bool', 'r_gzip', ''),
                        ('filetype_enable', 'bool', 'r_bz2', ''),
                        ('filetype_enable', 'bool', 'r_hqx', ''),
                        ('filetype_enable', 'bool', 'r_lzh', ''),
                        ('filetype_enable', 'bool', 'r_zip', ''),
                        ('filetype_enable', 'bool', 'r_rar', ''),
                        ('filetype_enable', 'bool', 'r_sit', ''),
                        ('filetype_enable', 'bool', 'r_tar', ''),
                        ('filetype_enable', 'bool', 'r_7z', ''),
                        ('filetype_enable', 'bool', 'r_iso', ''),
                        ('filetype_enable', 'bool', 'r_dmg', ''),
                        ('filetype_enable', 'bool', 'o_other', '1'),
                        ('filetype_enable', 'bool', 'o_swf', '1'),
                        ('filetype_enable', 'bool', 'o_blorb', '')
                        ");

    nel_setup_stuff_done($result);
}
