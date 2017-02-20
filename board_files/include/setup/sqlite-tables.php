<?php

function nel_create_post_table($dbh, $tables)
{
    if ($tables[POST_TABLE] === TRUE)
    {
        return;
    }

    echo 'Creating table ' . POST_TABLE . '...<br>';
    $result = $dbh->query('create table if not exists ' . POST_TABLE . ' (
            post_number         integer primary key,
            parent_thread       int unsigned not null default 0,
            name                varchar(255) default null,
            password            varchar(255) default null,
            tripcode            varchar(255) default null,
            secure_tripcode     varchar(255) default null,
            email               varchar(255) default null,
            subject             varchar(255) default null,
            comment             text default null,
            host                varbinary(16) default null,
            post_time           bigint not null default 0,
            has_file            tinyint not null default 0,
            file_count          tinyint unsigned not null default 0,
            external_content    tinyint not null default 0,
            external_count      tinyint unsigned not null default 0,
            license             varchar(255) default null,
            op                  tinyint not null default 0,
            sage                tinyint not null default 0,
            mod_post            tinyint not null default 0,
            mod_comment         varchar(255) default null
            )');

    if (!$result)
    {
        echo "\nPDO::errorInfo():\n";
        print_r($dbh->errorInfo());
        nel_table_fail($tables[POST_TABLE]);
    }
}

function nel_create_thread_table($dbh, $tables)
{
    if ($tables[THREAD_TABLE] === TRUE)
    {
        return;
    }

    echo 'Creating table ' . THREAD_TABLE . '...<br>';
    $result = $dbh->query('create table if not exists ' . THREAD_TABLE . ' (
            thread_id           integer primary key,
            first_post          int unsigned not null default 0,
            last_post           int unsigned not null default 0,
            total_files         tinyint unsigned not null default 0,
            total_external      tinyint unsigned not null default 0,
            last_update         bigint not null default 0,
            post_count          int not null default 0,
            thread_sage         tinyint not null default 0,
            sticky              tinyint not null default 0,
            archive_status      tinyint not null default 0,
            locked              tinyint not null default 0
            )');

    if (!$result)
    {
        nel_table_fail($tables[THREAD_TABLE]);
    }
}

function nel_create_file_table($dbh, $tables)
{
    if ($tables[FILE_TABLE] === TRUE)
    {
        return;
    }

    echo 'Creating table ' . FILE_TABLE . '...<br>';
    $result = $dbh->query('create table if not exists ' . FILE_TABLE . ' (
            auto_key        integer primary key,
            parent_thread   int unsigned default null,
            post_ref        int unsigned default null,
            file_order      tinyint unsigned not null default 1,
            supertype       varchar(255) default null,
            subtype         varchar(255) default null,
            mime            varchar(255) default null,
            filename        varchar(255) default null,
            extension       varchar(255) default null,
            image_width     int unsigned default null,
            image_height    int unsigned default null,
            preview_name    varchar(255) default null,
            preview_width   smallint unsigned default null,
            preview_height  smallint unsigned default null,
            filesize        int unsigned default 0,
            md5             binary(16) default null,
            sha1            binary(20) default null,
            source          varchar(255) default null,
            license         varchar(255) default null,
            exif            text default null,
            extra_meta      text default null
            )');

    if (!$result)
    {
        nel_table_fail($tables[FILE_TABLE]);
    }
}

/*function nel_create_external_content_table($dbh, $tables)
{
    if ($tables[EXTERNAL_TABLE] === TRUE)
    {
        return;
    }

    echo 'Creating table ' . EXTERNAL_TABLE . '...<br>';
    $result = $dbh->query('create table if not exists ' . EXTERNAL_TABLE . ' (
            auto_key        integer primary key,
            parent_thread   int unsigned default null,
            post_ref        int unsigned default null,
            file_order      tinyint unsigned not null default 1,
            supertype       varchar(255) default null,
            subtype         varchar(255) default null,
            mime            varchar(255) default null,
            filename        varchar(255) default null,
            extension       varchar(255) default null,
            image_width     int unsigned default null,
            image_height    int unsigned default null,
            preview_name    varchar(255) default null,
            preview_width   smallint unsigned default null,
            preview_height  smallint unsigned default null,
            filesize        int unsigned default 0,
            md5             binary(16) default null,
            sha1            binary(20) default null,
            source          varchar(255) default null,
            license         varchar(255) default null,
            exif            text default null,
            extra_meta      text default null
            )');

    if (!$result)
    {
        nel_table_fail($tables[EXTERNAL_TABLE]);
    }
}*/

function nel_create_external_content_table($dbh, $tables)
{
    if ($tables[EXTERNAL_TABLE] === TRUE)
    {
        return;
    }

    echo 'Creating table ' . EXTERNAL_TABLE . '...<br>';
    $result = $dbh->query('create table if not exists ' . EXTERNAL_TABLE . ' (
            auto_key        integer primary key,
            parent_thread   int unsigned default null,
            post_ref        int unsigned default null,
            content_order   tinyint unsigned not null default 1,
            content_type    varchar(255) default null,
            content_url     varchar(2048) default null,
            source          varchar(255) default null,
            license         varchar(255) default null
            )');

    if (!$result)
    {
        nel_table_fail($tables[EXTERNAL_TABLE]);
    }
}

function nel_create_archive_post_table($dbh, $tables)
{
    if ($tables[ARCHIVE_POST_TABLE] === TRUE)
    {
        return;
    }

    echo 'Creating table ' . ARCHIVE_POST_TABLE . '...<br>';
    $result = $dbh->query('create table if not exists ' . ARCHIVE_POST_TABLE . ' (
            post_number         integer primary key,
            parent_thread       int unsigned not null default 0,
            name                varchar(255) default null,
            password            varchar(255) default null,
            tripcode            varchar(255) default null,
            secure_tripcode     varchar(255) default null,
            email               varchar(255) default null,
            subject             varchar(255) default null,
            comment             text default null,
            host                varbinary(16) default null,
            post_time           bigint not null default 0,
            has_file            tinyint not null default 0,
            file_count          tinyint unsigned not null default 0,
            external_content    tinyint not null default 0,
            external_count      tinyint unsigned not null default 0,
            license             varchar(255) default null,
            op                  tinyint not null default 0,
            sage                tinyint not null default 0,
            mod_post            tinyint not null default 0,
            mod_comment         varchar(255) default null
            )');

    if (!$result)
    {
        nel_table_fail($tables[ARCHIVE_POST_TABLE]);
    }
}

function nel_create_archive_thread_table($dbh, $tables)
{
    if ($tables[ARCHIVE_THREAD_TABLE] === TRUE)
    {
        return;
    }

    echo 'Creating table ' . ARCHIVE_THREAD_TABLE . '...<br>';
    $result = $dbh->query('create table if not exists ' . ARCHIVE_THREAD_TABLE . ' (
            thread_id           integer primary key,
            first_post          int unsigned not null default 0,
            last_post           int unsigned not null default 0,
            total_files         tinyint unsigned not null default 0,
            total_external      tinyint unsigned not null default 0,
            last_update         bigint not null default 0,
            post_count          int not null default 0,
            thread_sage         tinyint not null default 0,
            sticky              tinyint not null default 0,
            archive_status      tinyint not null default 0,
            locked              tinyint not null default 0
            )');

    if (!$result)
    {
        nel_table_fail($tables[ARCHIVE_THREAD_TABLE]);
    }
}

function nel_create_archive_file_table($dbh, $tables)
{
    if ($tables[ARCHIVE_FILE_TABLE] === TRUE)
    {
        return;
    }

    echo 'Creating table ' . ARCHIVE_FILE_TABLE . '...<br>';
    $result = $dbh->query('create table if not exists ' . ARCHIVE_FILE_TABLE . ' (
            auto_key        integer primary key,
            parent_thread   int unsigned default null,
            post_ref        int unsigned default null,
            file_order      tinyint unsigned not null default 1,
            supertype       varchar(255) default null,
            subtype         varchar(255) default null,
            mime            varchar(255) default null,
            filename        varchar(255) default null,
            extension       varchar(255) default null,
            image_width     int unsigned default null,
            image_height    int unsigned default null,
            preview_name    varchar(255) default null,
            preview_width   smallint unsigned default null,
            preview_height  smallint unsigned default null,
            filesize        int unsigned default 0,
            md5             binary(16) default null,
            sha1            binary(20) default null,
            source          varchar(512) default null,
            license         varchar(255) default null,
            exif            text default null,
            extra_meta      text default null
            )');

    if (!$result)
    {
        nel_table_fail($tables[ARCHIVE_FILE_TABLE]);
    }
}

function nel_create_archive_external_content_table($dbh, $tables)
{
    if ($tables[ARCHIVE_EXTERNAL_TABLE] === TRUE)
    {
        return;
    }

    echo 'Creating table ' . ARCHIVE_EXTERNAL_TABLE . '...<br>';
    $result = $dbh->query('create table if not exists ' . ARCHIVE_EXTERNAL_TABLE . ' (
            auto_key        integer primary key,
            parent_thread   int unsigned default null,
            post_ref        int unsigned default null,
            content_order   tinyint unsigned not null default 1,
            content_type    varchar(255) default null,
            content_url     varchar(2048) default null,
            source          varchar(255) default null,
            license         varchar(255) default null
            )');

    if (!$result)
    {
        nel_table_fail($tables[ARCHIVE_EXTERNAL_TABLE]);
    }
}

function nel_create_ban_table($dbh, $tables)
{
    if ($tables[BAN_TABLE] === TRUE)
    {
        return;
    }

    echo 'Creating table ' . BAN_TABLE . '...<br>';
    $result = $dbh->query('create table if not exists ' . BAN_TABLE . ' (
            ban_id              integer primary key,
            type                varchar(255) default null,
            host                varbinary(16) default null,
            name                varchar(255) default null,
            reason              text default null,
            length              bigint not null default 0,
            ban_time            bigint not null default 0,
            appeal              text default null,
            appeal_response     text default null,
            appeal_status       tinyint not null default 0
            )');

    if (!$result)
    {
        nel_table_fail($tables[BAN_TABLE]);
    }
}

function nel_create_config_table($dbh, $tables)
{
    if ($tables[BAN_TABLE] === TRUE)
    {
        return;
    }

    echo 'Creating table ' . CONFIG_TABLE . '...<br>';
    $result = $dbh->query("create table if not exists " . CONFIG_TABLE . " (
            auto_key        integer primary key,
            config_type     varchar(255),
            data_type       tinyint not null default 0,
            config_name     varchar(255),
            setting         varchar(255)
            )");

    if (!$result)
    {
        nel_table_fail($tables[CONFIG_TABLE]);
    }

    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('technical', '2', 'initial_schema', '002')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('technical', '2', 'current_schema', '002')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'allow_tripkeys', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'force_anonymous', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'show_title', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'show_favicon', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'show_logo', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'use_thumb', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'use_magick', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'use_file_icon', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'use_png_thumb', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'require_image_start', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'require_image_always', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'allow_multifile', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'allow_op_multifile', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'use_new_imgdel', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'use_fgsfds', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '1', 'use_spambot_trap', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '3', 'board_name', 'Nelliel-powered image board')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '3', 'board_favicon', '')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '3', 'board_logo', '')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'thread_delay', '60')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'reply_delay', '15')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'abbreviate_thread', '5')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_post_files', '3')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_files_row', '3')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_multi_width', '175')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_multi_height', '175')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'jpeg_quality', '85')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_width', '256')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_height', '256')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_filesize', '1024')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_name_length', '100')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_email_length', '100')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_subject_length', '100')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_comment_length', '1000')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_comment_lines', '25')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_source_length', '250')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_license_length', '100')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'threads_per_page', '10')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'page_limit', '10')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'page_buffer', '5')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_posts', '1000')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '2', 'max_bumps', '750')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '3', 'tripkey_marker', '!')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '3', 'date_format', 'ISO')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '3', 'old_threads', 'ARCHIVE')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '3', 'date_separator', '/')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('board_setting', '3', 'fgsfds_name', 'FGSFDS')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'graphics_enable', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'graphics_jpeg', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'graphics_gif', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'graphics_png', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'graphics_jpeg2000', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'graphics_tiff', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'graphics_bmp', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'graphics_ico', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'graphics_psd', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'graphics_tga', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'graphics_pict', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'graphics_art', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'audio_enable', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'audio_wav', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'audio_aiff', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'audio_mp3', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'audio_m4a', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'audio_flac', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'audio_aac', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'audio_ogg', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'audio_au', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'audio_wma', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'audio_midi', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'audio_ac3', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'video_enable', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'video_mpeg', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'video_mov', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'video_avi', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'video_wmv', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'video_mp4', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'video_mkv', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'video_flv', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'document_enable', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'document_rtf', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'document_pdf', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'document_doc', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'document_ppt', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'document_xls', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'document_txt', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'other_enable', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'other_swf', '1')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'other_blorb', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'archive_enable', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'archive_gzip', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'archive_bz2', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'archive_hqx', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'archive_lzh', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'archive_zip', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'archive_rar', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'archive_sit', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'archive_tar', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'archive_7z', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'archive_iso', '0')");
    $dbh->query("INSERT INTO " . CONFIG_TABLE . " (config_type, data_type, config_name, setting) VALUES ('filetype', '1', 'archive_dmg', '0')");
}
?>
