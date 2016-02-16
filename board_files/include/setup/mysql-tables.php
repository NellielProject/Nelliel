<?php
function nel_create_post_table($dbh, $tables)
{
    if ($tables[POST_TABLE] === TRUE)
    {
        return;
    }
    
    echo 'Creating table ' . POST_TABLE . '...<br>';
    $result = $dbh->query('create table if not exists ' . POST_TABLE . ' (
            post_number         int unsigned not null auto_increment primary key,
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
            external_content    tinyint not null default 0,
            has_file            tinyint not null default 0,
            file_count          tinyint unsigned not null default 0,
            op                  tinyint not null default 0,
            sage                tinyint not null default 0,
            mod_post            tinyint not null default 0,
            mod_comment         varchar(255) default null
            )');
    
    if (!$result)
    {
        nel_table_fail($tables[POST_TABLE]);
    }

    $result = $dbh->query('ALTER TABLE ' . POST_TABLE . ' CONVERT TO CHARACTER SET utf8');
        
    if (check_engines($dbh, 'InnoDB') === TRUE)
    {
        $result = $dbh->query('ALTER TABLE ' . POST_TABLE . ' ENGINE=InnoDB');
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
            thread_id           int unsigned not null primary key,
            first_post          int unsigned not null default 0,
            last_post           int unsigned not null default 0,
            total_files         tinyint unsigned not null default 0,
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
    
    $result = $dbh->query('ALTER TABLE ' . THREAD_TABLE . ' CONVERT TO CHARACTER SET utf8');
        
    if (check_engines($dbh, 'InnoDB') === TRUE)
    {
        $result = $dbh->query('ALTER TABLE ' . THREAD_TABLE . ' ENGINE=InnoDB');
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
            auto_key        int unsigned not null auto_increment primary key,
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
    
    $result = $dbh->query('ALTER TABLE ' . FILE_TABLE . ' CONVERT TO CHARACTER SET utf8');
        
    if (check_engines($dbh, 'InnoDB') === TRUE)
    {
        $result = $dbh->query('ALTER TABLE ' . FILE_TABLE . ' ENGINE=InnoDB');
    }
}

function nel_create_external_content_table($dbh, $tables)
{
    if ($tables[EXTERNAL_TABLE] === TRUE)
    {
        return;
    }

    echo 'Creating table ' . EXTERNAL_TABLE . '...<br>';
    $result = $dbh->query('create table if not exists ' . EXTERNAL_TABLE . ' (
            auto_key        int unsigned not null auto_increment primary key,
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

    $result = $dbh->query('ALTER TABLE ' . EXTERNAL_TABLE . ' CONVERT TO CHARACTER SET utf8');

    if (check_engines($dbh, 'InnoDB') === TRUE)
    {
        $result = $dbh->query('ALTER TABLE ' . EXTERNAL_TABLE . ' ENGINE=InnoDB');
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
            post_number         int unsigned not null primary key,
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
            external_content    tinyint not null default 0,
            content_url         varchar(2048) default null,
            has_file            tinyint not null default 0,
            file_count          tinyint unsigned not null default 0,
            sage                tinyint not null default 0,
            mod_post            tinyint not null default 0,
            mod_comment         varchar(255) default null
            )');
    
    if (!$result)
    {
        nel_table_fail($tables[ARCHIVE_POST_TABLE]);
    }
    
    $result = $dbh->query('ALTER TABLE ' . ARCHIVE_POST_TABLE . ' CONVERT TO CHARACTER SET utf8');
        
    if (check_engines($dbh, 'InnoDB') === TRUE)
    {
        $result = $dbh->query('ALTER TABLE ' . ARCHIVE_POST_TABLE . ' ENGINE=InnoDB');
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
            thread_id           int unsigned not null primary key,
            first_post          int unsigned not null default 0,
            last_post           int unsigned not null default 0,
            total_files         tinyint unsigned not null default 0,
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
    
    $result = $dbh->query('ALTER TABLE ' . ARCHIVE_THREAD_TABLE . ' CONVERT TO CHARACTER SET utf8');
        
    if (check_engines($dbh, 'InnoDB') === TRUE)
    {
        $result = $dbh->query('ALTER TABLE ' . ARCHIVE_THREAD_TABLE . ' ENGINE=InnoDB');
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
            auto_key        int unsigned not null primary key,
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
        nel_table_fail($tables[ARCHIVE_FILE_TABLE]);
    }

    $result = $dbh->query('ALTER TABLE ' . ARCHIVE_FILE_TABLE . ' CONVERT TO CHARACTER SET utf8');
        
    if (check_engines($dbh, 'InnoDB') === TRUE)
    {
        $result = $dbh->query('ALTER TABLE ' . ARCHIVE_FILE_TABLE . ' ENGINE=InnoDB');
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
            auto_key        int unsigned not null primary key,
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

    $result = $dbh->query('ALTER TABLE ' . ARCHIVE_EXTERNAL_TABLE . ' CONVERT TO CHARACTER SET utf8');

    if (check_engines($dbh, 'InnoDB') === TRUE)
    {
        $result = $dbh->query('ALTER TABLE ' . ARCHIVE_EXTERNAL_TABLE . ' ENGINE=InnoDB');
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
            ban_id              int unsigned not null auto_increment primary key,
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

    $result = $dbh->query('ALTER TABLE ' . BAN_TABLE . ' CONVERT TO CHARACTER SET utf8');
}

function nel_create_config_table($dbh, $tables)
{
    if ($tables[BAN_TABLE] === TRUE)
    {
        return;
    }

    echo 'Creating table ' . CONFIG_TABLE . '...<br>';
    $result = $dbh->query("create table if not exists " . CONFIG_TABLE . " (
            auto_key        int unsigned not null auto_increment primary key,
            config_type     varchar(255),
            config_name     varchar(255),
            setting         varchar(255)
            )");
    
    if (!$result)
    {
        nel_table_fail($tables[CONFIG_TABLE]);
    }

    $result = $dbh->query('ALTER TABLE ' . CONFIG_TABLE . ' CONVERT TO CHARACTER SET utf8');
    
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('technical', 'original_database_version', '002')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('technical', 'current_database_version', '002')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'allow_tripkeys', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'force_anonymous', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'show_title', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'show_favicon', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'show_logo', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'use_thumb', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'use_magick', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'use_file_icon', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'use_png_thumb', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'require_image_start', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'require_image_always', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'allow_multifile', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'allow_op_multifile', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'use_new_imgdel', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'use_fgsfds', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_bool', 'use_spambot_trap', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'board_name', 'Nelliel-powered image board')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'board_favicon', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'board_logo', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'thread_delay', '60')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'reply_delay', '15')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'abbreviate_thread', '5')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_post_files', '3')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_files_row', '3')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_multi_width', '175')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_multi_height', '175')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'jpeg_quality', '85')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_width', '256')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_height', '256')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_filesize', '1024')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_name_length', '100')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_email_length', '100')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_subject_length', '100')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_comment_length', '1000')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_comment_lines', '25')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_source_length', '250')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_license_length', '100')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'threads_per_page', '10')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'page_limit', '10')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'page_buffer', '5')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_posts', '1000')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_bumps', '750')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'tripkey_marker', '!')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'date_format', 'ISO')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'old_threads', 'ARCHIVE')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'date_separator', '/')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'fgsfds_name', 'FGSFDS')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_graphics', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_jpeg', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_gif', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_png', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_jpeg2000', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_tiff', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_bmp', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_ico', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_psd', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_tga', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_pict', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_art', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_audio', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_wav', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_aiff', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_mp3', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_m4a', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_flac', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_aac', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_ogg', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_au', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_wma', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_midi', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_ac3', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_video', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_mpeg', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_mov', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_avi', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_wmv', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_mp4', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_mkv', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_flv', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_document', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_rtf', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_pdf', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_doc', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_ppt', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_xls', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_txt', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_o', 'enable_other', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_o', 'enable_swf', '1')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_o', 'enable_blorb', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_archive', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_gzip', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_bz2', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_hqx', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_lzh', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_zip', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_rar', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_sit', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_tar', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_7z', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_iso', '')");
    $dbh->query("INSERT INTO `" . CONFIG_TABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_dmg', '')");
}
?>