<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// First run - checks for database, directories
// If anything does not exist yet, create it
//
function table_exists($table, $dbh)
{
    if (SQLTYPE === 'SQLITE')
    {
        $result = $dbh->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name='" . $table . "'");
    }

    if (SQLTYPE === 'MYSQL')
    {
        $result = $dbh->query("SHOW TABLES FROM `" . SQLDB . "` LIKE '" . $table . "'");
    }

    $test = $result->fetch(PDO::FETCH_NUM);

    if ($test[0] == $table)
    {
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

function setup_check($dbh)
{
    $stuff_done = FALSE;

    if (!table_exists(POSTTABLE, $dbh))
    {
        echo 'Creating table ' . POSTTABLE . '...<br>';
        $result = $dbh->query("create table if not exists " . POSTTABLE . " (
			post_number		INTEGER PRIMARY KEY,
			name	varchar(255) default null,
			tripcode	varchar(255) default null,
			secure_tripcode	varchar(255) default null,
			email	varchar(255) default null,
			subject		varchar(255) default null,
			comment		text default null,
			host	varbinary(16) default null,
			password		char(16) not null default '',
			post_time	char(14) not null default 0,
			has_file tinyint(1) not null default 0,
			last_update	char(14) not null default 0,
			response_to	int unsigned not null default 0,
			last_response	int unsigned default null,
			post_count	int unsigned not null default 0,
			sticky	tinyint(1) not null default 0,
			mod_post	tinyint(1) not null default 0,
			mod_comment	text default null,
			archive_status tinyint(1) not null default 0,
			locked tinyint(1) not null default 0
		)");

        if (!$result)
        {
            die('Creation of ' . POSTTABLE . ' failed! Check database settings and config.php then retry installation.');
        }
        else
        {
            if (SQLTYPE === 'SQLITE')
            {
                ;
            }

            if (SQLTYPE === 'MYSQL')
            {
                $result = $dbh->query("ALTER TABLE '" . POSTTABLE . "' CONVERT TO CHARACTER SET utf8");
            }

            $stuff_done = TRUE;
        }
    }

    if (!table_exists(FILETABLE, $dbh))
    {
        echo 'Creating table ' . FILETABLE . '...<br>';
        $result = $dbh->query("create table if not exists " . FILETABLE . " (
			parent_thread	int default null,
			post_ref		int default null,
			file_order		int not null default 1,
			supertype varchar(12) default null,
			subtype	varchar(12) default null,
			mime	varchar(255) default null,
			filename	varchar(255) default null,
			extension		varchar(255) default null,
			image_width		int unsigned null,
			image_height	int unsigned null,
			preview_name	varchar(255) default null,
			preview_width	smallint unsigned null,
			preview_height	smallint unsigned null,
			filesize	int unsigned null,
			md5		char(32) default null,
			source	varchar(255) default null,
			license	varchar(255) default null,
			exif	text default null,
			extra_meta	text default null
			)");

        if (!$result)
        {
            print_r($dbh->errorInfo());
            die('Creation of ' . FILETABLE . ' failed! Check database settings and config.php then retry installation.');
        }
        else
        {
            if (SQLTYPE === 'SQLITE')
            {
                ;
            }

            if (SQLTYPE === 'MYSQL')
            {
                $result = $dbh->query("ALTER TABLE '" . FILETABLE . "' CONVERT TO CHARACTER SET utf8");
            }

            $stuff_done = TRUE;
        }
    }

    if (!table_exists(ARCHIVETABLE, $dbh))
    {
        echo 'Creating table ' . ARCHIVETABLE . '...<br>';
        $result = $dbh->query("create table if not exists " . ARCHIVETABLE . " (
			post_number		int,
			name	varchar(255) default null,
			tripcode	varchar(255) default null,
			secure_tripcode	varchar(255) default null,
			email	varchar(255) default null,
			subject		varchar(255) default null,
			comment		text default null,
			host	varbinary(16) default null,
			password		char(16) not null default '',
			post_time	char(14) not null default 0,
			has_file tinyint(1) not null default 0,
			last_update	char(14) not null default 0,
			response_to	int unsigned not null default 0,
			last_response	int unsigned default null,
			post_count	int unsigned not null default 0,
			sticky	tinyint(1) not null default 0,
			mod_post	tinyint(1) not null default 0,
			mod_comment	text default null,
			archive_status tinyint(1) not null default 0,
			locked tinyint(1) not null default 0,
			primary key(post_number)
			)");

        if (!$result)
        {
            die('Creation of ' . ARCHIVETABLE . ' failed! Check database settings and config.php then retry installation.');
        }
        else
        {
            if (SQLTYPE === 'SQLITE')
            {
                ;
            }

            if (SQLTYPE === 'MYSQL')
            {
                $result = $dbh->query("ALTER TABLE '" . ARCHIVETABLE . "' CONVERT TO CHARACTER SET utf8");
            }

            $stuff_done = TRUE;
        }
    }

    if (!table_exists(ARCHIVEFILETABLE, $dbh))
    {
        echo 'Creating table ' . ARCHIVEFILETABLE . '...<br>';
        $result = $dbh->query("create table if not exists " . ARCHIVEFILETABLE . " (
			parent_thread	int default null,
			post_ref		int default null,
			file_order		int not null default 1,
			supertype varchar(12) default null,
			subtype	varchar(12) default null,
			mime	varchar(255) default null,
			filename	varchar(255) default null,
			extension		varchar(255) default null,
			image_width		int default null,
			image_height	int default null,
			preview_name	varchar(255) default null,
			preview_width	int default null,
			preview_height	int default null,
			filesize	int unsigned null,
			md5		char(32) default null,
			source	varchar(255) default null,
			license	varchar(255) default null,
			exif	text default null,
			extra_meta	text default null
			)");

        if (!$result)
        {
            die('Creation of ' . ARCHIVEFILETABLE . ' failed! Check database settings and config.php then retry installation.');
        }
        else
        {
            if (SQLTYPE === 'SQLITE')
            {
                ;
            }

            if (SQLTYPE === 'MYSQL')
            {
                $result = $dbh->query("ALTER TABLE '" . ARCHIVEFILETABLE . "' CONVERT TO CHARACTER SET utf8");
            }

            $stuff_done = TRUE;
        }
    }

    if (!table_exists(CONFIGTABLE, $dbh))
    {
        echo 'Creating table ' . CONFIGTABLE . '...<br>';
        $result = $dbh->query("create table if not exists " . CONFIGTABLE . " (
			config_type		varchar(255),
			config_name	varchar(255),
			setting		varchar(255),
			primary key(config_name)
			)");

        if (!$result)
        {
            die('Creation of ' . CONFIGTABLE . ' failed! Check database settings and config.php then retry installation.');
        }
        else
        {
            if (SQLTYPE === 'SQLITE')
            {
                ;
            }

            if (SQLTYPE === 'MYSQL')
            {
                $result = $dbh->query("ALTER TABLE '" . CONFIGTABLE . "' CONVERT TO CHARACTER SET utf8");
            }

            $stuff_done = TRUE;
        }

        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('technical', 'original_database_version', '001')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('technical', 'current_database_version', '001')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'allow_tripkeys', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'force_anonymous', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'show_title', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'show_favicon', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'show_logo', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'use_thumb', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'use_magick', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'use_file_icon', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'use_png_thumb', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'require_image_start', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'require_image_always', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'allow_multifile', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'allow_op_multifile', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'use_new_imgdel', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'use_fgsfds', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting_1bit', 'use_spambot_trap', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'board_name', 'Nelliel-powered image board')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'board_favicon', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'board_logo', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'thread_delay', '60')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'reply_delay', '15')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'abbreviate_thread', '5')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_post_files', '3')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_files_row', '3')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_multi_width', '175')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_multi_height', '175')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'jpeg_quality', '85')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_width', '256')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_height', '256')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_filesize', '1024')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_name_length', '100')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_email_length', '100')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_subject_length', '100')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_comment_length', '1000')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_comment_lines', '25')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_source_length', '250')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_license_length', '100')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'threads_per_page', '10')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'page_limit', '10')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'page_buffer', '5')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_posts', '1000')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'max_bumps', '750')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'tripkey_marker', '!')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'date_format', 'ISO')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'old_threads', 'ARCHIVE')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'date_separator', '/')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('board_setting', 'fgsfds_name', 'FGSFDS')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_graphics', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_jpeg', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_gif', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_png', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_jpeg2000', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_tiff', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_bmp', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_ico', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_psd', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_tga', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_pict', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_g', 'enable_art', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_audio', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_wav', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_aiff', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_mp3', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_m4a', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_flac', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_aac', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_ogg', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_au', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_wma', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_midi', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_a', 'enable_ac3', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_video', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_mpeg', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_mov', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_avi', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_wmv', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_mp4', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_mkv', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_v', 'enable_flv', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_document', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_rtf', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_pdf', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_doc', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_ppt', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_xls', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_d', 'enable_txt', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_o', 'enable_other', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_o', 'enable_swf', '1')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_o', 'enable_blorb', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_archive', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_gzip', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_bz2', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_hqx', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_lzh', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_zip', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_rar', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_sit', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_tar', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_7z', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_iso', '')");
        $dbh->query("INSERT INTO `" . CONFIGTABLE . "` (`config_type`, `config_name`, `setting`) VALUES ('filetype_allow_r', 'enable_dmg', '')");
    }

    if (!table_exists(BANTABLE, $dbh))
    {
        echo 'Creating table ' . BANTABLE . '...<br>';
        $result = $dbh->query("create table if not exists " . BANTABLE . " (
			id		INTEGER PRIMARY KEY,
			type	varchar(50) default null,
			host	varbinary(16) default null,
			name	varchar(100) default null,
			reason	varchar(255) default null,
			length	int default null,
			ban_time	int not null default 0,
			appeal	text default null,
			appeal_response	text default null,
			appeal_status	tinyint not null default 0
			)");

        if (!$result)
        {
            die('Creation of ' . BANTABLE . ' failed! Check database settings and config.php then retry installation.');
        }
        else
        {
            if (SQLTYPE === 'SQLITE')
            {
                ;
            }

            if (SQLTYPE === 'MYSQL')
            {
                $result = $dbh->query("ALTER TABLE '" . BANTABLE . "' CONVERT TO CHARACTER SET utf8");
            }

            $stuff_done = TRUE;
        }
    }

    if (!file_exists(SRC_PATH))
    {
        echo 'Creating directory ' . SRC_DIR . '<br>';
        if (mkdir(SRC_PATH, 0755))
        {
            chmod(SRC_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . SRC_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(THUMB_PATH))
    {
        echo 'Creating directory ' . THUMB_DIR . '<br>';
        if (mkdir(THUMB_PATH, 0755))
        {
            chmod(THUMB_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . THUMB_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(PAGE_PATH))
    {
        echo 'Creating directory ' . PAGE_DIR . '<br>';
        if (mkdir(PAGE_PATH, 0755))
        {
            chmod(PAGE_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . PAGE_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(CACHE_PATH))
    {
        echo 'Creating directory ' . CACHE_DIR . '<br>';
        if (mkdir(CACHE_PATH, 0755))
        {
            chmod(CACHE_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . CACHE_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(ARCHIVE_PATH))
    {
        echo 'Creating directory ' . ARCHIVE_DIR . '<br>';
        if (mkdir(ARCHIVE_PATH, 0755))
        {
            chmod(ARCHIVE_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . ARCHIVE_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(ARC_SRC_PATH))
    {
        echo 'Creating directory ' . ARCHIVE_DIR . SRC_DIR . '<br>';
        if (mkdir(ARC_SRC_PATH, 0755))
        {
            chmod(ARC_SRC_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . ARCHIVE_DIR . SRC_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(ARC_THUMB_PATH))
    {
        echo 'Creating directory ' . ARCHIVE_DIR . THUMB_DIR . '<br>';
        if (mkdir(ARC_THUMB_PATH, 0755))
        {
            chmod(ARC_THUMB_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . ARCHIVE_DIR . THUMB_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if (!file_exists(ARC_PAGE_PATH))
    {
        echo 'Creating directory ' . ARCHIVE_DIR . PAGE_DIR . '<br>';
        if (mkdir(ARC_PAGE_PATH, 0755))
        {
            chmod(ARC_PAGE_PATH, 0755);
            $stuff_done = TRUE;
        }
        else
        {
            die('Could not create ' . ARCHIVE_DIR . PAGE_DIR . ' directory. Check permissions and config.php settings then retry installation.');
        }
    }

    if ($stuff_done)
    {
        define('STUFF_DONE', TRUE);
        echo '<br><br>Process completed. If there are no errors listed above then you did it right. Please wait a few seconds and you will be taken to the front page.';
    }
    else
    {
        define('STUFF_DONE', FALSE);
    }
}

function generate_auth_file($plugins)
{
    if (!file_exists(FILES_PATH . '/auth_data.nel.php'))
    {
        if (DEFAULTADMIN !== '' && DEFAULTADMIN_PASS !== '')
        {
            echo 'Creating auth file...';
            $new_auth = '<?php
$authorized = array(
	\'' . DEFAULTADMIN . '\' => array(
        \'settings\' => array(
            \'staff_password\' => \'' . nel_hash(DEFAULTADMIN_PASS, $plugins) . '\',
            \'staff_type\' => \'admin\',
            \'staff_trip\' => \'\'),
	    \'perms\' => array(
            \'perm_config\' => TRUE,
            \'perm_staff_panel\' => TRUE,
            \'perm_ban_panel\' => TRUE,
            \'perm_thread_panel\' => TRUE,
            \'perm_mod_mode\' => TRUE,
            \'perm_ban\' => TRUE,
            \'perm_delete\' => TRUE,
            \'perm_post\' => TRUE,
            \'perm_post_anon\' => TRUE,
            \'perm_sticky\' => TRUE,
            \'perm_update_pages\' => TRUE,
            \'perm_update_cache\' => TRUE
		)),
	); ?>';

            if (nel_write_file(FILES_PATH . 'auth_data.nel.php', $new_auth, 0644))
            {
                $stuff_done = TRUE;
            }
            else
            {
                die('Could not create auth file. Check permissions and config.php then retry installation.');
            }
        }
        else
        {
            $stuff_done = TRUE;
            echo 'ERROR: Could not create auth file due to invalid or missing admin info. The board will probably work but you will have no administrative abilities. Check your config.php then retry installation.';
        }
    }
}
?>
