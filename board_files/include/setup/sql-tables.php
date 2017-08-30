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
        if($int_column === 'SMALLINT')
        {
            $int_column = 'SMALLSERIAL';
        }

        if($int_column === 'INTEGER')
        {
            $int_column = 'SERIAL';
        }

        if($int_column === 'BIGINT')
        {
            $int_column = 'BIGSERIAL';
        }
    }
    else if(SQLTYPE === 'SQLITE')
    {
        $auto = 'AUTOINCREMENT';
    }

    return $int_column . ' ' . $auto;
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

function nel_create_table_query($schema, $table_name)
{
    $dbh = nel_get_db_handle();

    if(nel_table_exists($table_name))
    {
        return false;
    }

    $result = $dbh->query($schema);

    if (!$result)
    {
        print_r($dbh->errorInfo());
        nel_table_fail($table_name);
    }

    return $result;
}

function nel_create_posts_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "post_number"       ' . $auto_inc . ' NOT NULL PRIMARY KEY,
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
        "mod_post"          SMALLINT NOT NULL DEFAULT 0,
        "mod_comment"       VARCHAR(255) DEFAULT NULL,
        "post_hash"         CHAR(40) DEFAULT NULL
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);
}

function nel_create_threads_table($table_name)
{
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "thread_id"         INTEGER NOT NULL PRIMARY KEY,
        "first_post"        INTEGER NOT NULL DEFAULT 0,
        "last_post"         INTEGER NOT NULL DEFAULT 0,
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
}

function nel_create_files_table($table_name)
{
    $dbh = nel_get_db_handle();
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
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
}

function nel_create_external_table($table_name)
{
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "parent_thread"     INTEGER NOT NULL DEFAULT 0,
        "post_ref"          INTEGER NOT NULL DEFAULT 0,
        "content_order"     SMALLINT NOT NULL DEFAULT 1,
        "content_type"      VARCHAR(255) DEFAULT NULL,
        "content_url"       VARCHAR(2048) DEFAULT NULL,
        "source"            VARCHAR(255) DEFAULT NULL,
        "license"           VARCHAR(255) DEFAULT NULL
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);
}

function nel_create_bans_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "ban_id"            ' . $auto_inc . ' NOT NULL PRIMARY KEY,
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
}

function nel_create_config_table($table_name)
{
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "config_type"       VARCHAR(255) DEFAULT NULL,
        "data_type"         SMALLINT NOT NULL DEFAULT 0,
        "config_name"       VARCHAR(255) DEFAULT NULL UNIQUE,
        "setting"           VARCHAR(255) DEFAULT NULL
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);
    nel_insert_config_defaults();
}

function nel_create_user_table($table_name)
{
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "user_id"          VARCHAR(255) DEFAULT NULL UNIQUE,
        "user_password"    VARCHAR(255) DEFAULT NULL,
        "role_id"           VARCHAR(255) DEFAULT NULL,
        "active"            SMALLINT NOT NULL DEFAULT 0,
        "failed_logins"     SMALLINT NOT NULL DEFAULT 0,
        "last_failed_login" INT NOT NULL DEFAULT 0
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);
}

function nel_create_roles_table($table_name)
{
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "role_id"               VARCHAR(255) DEFAULT NULL UNIQUE,
        "role_title"            VARCHAR(255) DEFAULT NULL,
        "role_level"            SMALLINT NOT NULL DEFAULT 0,
        "capcode_text"          VARCHAR(255) DEFAULT NULL,
        "posting_tripcode"      VARCHAR(255) DEFAULT NULL,
        "perm_board_config"     SMALLINT NOT NULL DEFAULT 0,
        "perm_staff_add"        SMALLINT NOT NULL DEFAULT 0,
        "perm_staff_modify"     SMALLINT NOT NULL DEFAULT 0,
        "perm_ban_add"          SMALLINT NOT NULL DEFAULT 0,
        "perm_ban_modify"       SMALLINT NOT NULL DEFAULT 0,
        "perm_post_modify"      SMALLINT NOT NULL DEFAULT 0,
        "perm_post_anon"        SMALLINT NOT NULL DEFAULT 0,
        "perm_post_named"       SMALLINT NOT NULL DEFAULT 0,
        "perm_post_locked"      SMALLINT NOT NULL DEFAULT 0,
        "perm_regen_caches"     SMALLINT NOT NULL DEFAULT 0,
        "perm_regen_index"      SMALLINT NOT NULL DEFAULT 0,
        "perm_regen_thread"     SMALLINT NOT NULL DEFAULT 0
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);
}

function nel_insert_config_defaults()
{
    $dbh = nel_get_db_handle();
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, config_name, setting)
                VALUES  ('technical', 'original_schema_version', '002'),
                        ('technical', 'current_schema_version', '002'),
                        ('board_setting_1bit', 'allow_tripkeys', '1'),
                        ('board_setting_1bit', 'force_anonymous', ''),
                        ('board_setting_1bit', 'show_title', '1'),
                        ('board_setting_1bit', 'show_favicon', ''),
                        ('board_setting_1bit', 'show_logo', ''),
                        ('board_setting_1bit', 'use_thumb', '1'),
                        ('board_setting_1bit', 'use_magick', ''),
                        ('board_setting_1bit', 'use_file_icon', '1'),
                        ('board_setting_1bit', 'use_png_thumb', ''),
                        ('board_setting_1bit', 'require_image_start', '1'),
                        ('board_setting_1bit', 'require_image_always', ''),
                        ('board_setting_1bit', 'allow_multifile', ''),
                        ('board_setting_1bit', 'allow_op_multifile', ''),
                        ('board_setting_1bit', 'use_new_imgdel', '1'),
                        ('board_setting_1bit', 'use_fgsfds', '1'),
                        ('board_setting_1bit', 'use_spambot_trap', '1'),
                        ('board_setting', 'board_name', 'Nelliel-powered image board'),
                        ('board_setting', 'board_favicon', ''),
                        ('board_setting', 'board_logo', ''),
                        ('board_setting', 'thread_delay', '60'),
                        ('board_setting', 'reply_delay', '15'),
                        ('board_setting', 'abbreviate_thread', '5'),
                        ('board_setting', 'max_post_files', '3'),
                        ('board_setting', 'max_files_row', '3'),
                        ('board_setting', 'max_multi_width', '175'),
                        ('board_setting', 'max_multi_height', '175'),
                        ('board_setting', 'jpeg_quality', '85'),
                        ('board_setting', 'max_width', '256'),
                        ('board_setting', 'max_height', '256'),
                        ('board_setting', 'max_filesize', '1024'),
                        ('board_setting', 'max_name_length', '100'),
                        ('board_setting', 'max_email_length', '100'),
                        ('board_setting', 'max_subject_length', '100'),
                        ('board_setting', 'max_comment_length', '1000'),
                        ('board_setting', 'max_comment_lines', '25'),
                        ('board_setting', 'max_source_length', '250'),
                        ('board_setting', 'max_license_length', '100'),
                        ('board_setting', 'threads_per_page', '10'),
                        ('board_setting', 'page_limit', '10'),
                        ('board_setting', 'page_buffer', '5'),
                        ('board_setting', 'max_posts', '1000'),
                        ('board_setting', 'max_bumps', '750'),
                        ('board_setting', 'tripkey_marker', '!'),
                        ('board_setting', 'date_format', 'ISO'),
                        ('board_setting', 'old_threads', 'ARCHIVE'),
                        ('board_setting', 'date_separator', '/'),
                        ('board_setting', 'fgsfds_name', 'FGSFDS'),
                        ('filetype_allow_g', 'enable_graphics', '1'),
                        ('filetype_allow_g', 'enable_jpeg', '1'),
                        ('filetype_allow_g', 'enable_gif', '1'),
                        ('filetype_allow_g', 'enable_png', '1'),
                        ('filetype_allow_g', 'enable_jpeg2000', '1'),
                        ('filetype_allow_g', 'enable_tiff', ''),
                        ('filetype_allow_g', 'enable_bmp', '1'),
                        ('filetype_allow_g', 'enable_ico', ''),
                        ('filetype_allow_g', 'enable_psd', ''),
                        ('filetype_allow_g', 'enable_tga', ''),
                        ('filetype_allow_g', 'enable_pict', ''),
                        ('filetype_allow_g', 'enable_art', ''),
                        ('filetype_allow_a', 'enable_audio', ''),
                        ('filetype_allow_a', 'enable_wav', ''),
                        ('filetype_allow_a', 'enable_aiff', ''),
                        ('filetype_allow_a', 'enable_mp3', ''),
                        ('filetype_allow_a', 'enable_m4a', ''),
                        ('filetype_allow_a', 'enable_flac', ''),
                        ('filetype_allow_a', 'enable_aac', ''),
                        ('filetype_allow_a', 'enable_ogg', ''),
                        ('filetype_allow_a', 'enable_au', ''),
                        ('filetype_allow_a', 'enable_wma', ''),
                        ('filetype_allow_a', 'enable_midi', ''),
                        ('filetype_allow_a', 'enable_ac3', ''),
                        ('filetype_allow_v', 'enable_video', ''),
                        ('filetype_allow_v', 'enable_mpeg', ''),
                        ('filetype_allow_v', 'enable_mov', ''),
                        ('filetype_allow_v', 'enable_avi', ''),
                        ('filetype_allow_v', 'enable_wmv', ''),
                        ('filetype_allow_v', 'enable_mp4', ''),
                        ('filetype_allow_v', 'enable_mkv', ''),
                        ('filetype_allow_v', 'enable_flv', ''),
                        ('filetype_allow_d', 'enable_document', ''),
                        ('filetype_allow_d', 'enable_rtf', ''),
                        ('filetype_allow_d', 'enable_pdf', ''),
                        ('filetype_allow_d', 'enable_doc', ''),
                        ('filetype_allow_d', 'enable_ppt', ''),
                        ('filetype_allow_d', 'enable_xls', ''),
                        ('filetype_allow_d', 'enable_txt', ''),
                        ('filetype_allow_o', 'enable_other', '1'),
                        ('filetype_allow_o', 'enable_swf', '1'),
                        ('filetype_allow_o', 'enable_blorb', ''),
                        ('filetype_allow_r', 'enable_archive', ''),
                        ('filetype_allow_r', 'enable_gzip', ''),
                        ('filetype_allow_r', 'enable_bz2', ''),
                        ('filetype_allow_r', 'enable_hqx', ''),
                        ('filetype_allow_r', 'enable_lzh', ''),
                        ('filetype_allow_r', 'enable_zip', ''),
                        ('filetype_allow_r', 'enable_rar', ''),
                        ('filetype_allow_r', 'enable_sit', ''),
                        ('filetype_allow_r', 'enable_tar', ''),
                        ('filetype_allow_r', 'enable_7z', ''),
                        ('filetype_allow_r', 'enable_iso', ''),
                        ('filetype_allow_r', 'enable_dmg', '')
                        ");
}