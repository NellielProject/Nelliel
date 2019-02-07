<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableBoardConfig extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = '_config';
        $this->columns = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => true],
            'config_type' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'config_owner' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'config_category' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'data_type' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'config_name' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'setting' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'select_type' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => false],
            'edit_lock' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => false]];
        $this->splitColumnInfo();
        $this->schema_version = 1;
    }

    public function setup()
    {
        $this->createTable();
    }

    public function createTable(array $other_tables = null)
    {
        $auto_inc = $this->sql_helpers->autoincrementColumn('INTEGER');
        $options = $this->sql_helpers->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry                   " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            config_type         VARCHAR(255) DEFAULT NULL,
            config_owner        VARCHAR(255) NOT NULL,
            config_category     VARCHAR(255) DEFAULT NULL,
            data_type           VARCHAR(255) DEFAULT NULL,
            config_name         VARCHAR(255) NOT NULL,
            setting             VARCHAR(255) NOT NULL,
            select_type         SMALLINT NOT NULL DEFAULT 0,
            edit_lock           SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'allow_tripkeys', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'force_anonymous', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'show_board_title', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'show_board_favicon', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'show_board_banner', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_thumb', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_magick', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_file_icon', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_png_thumb', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'animated_gif_preview', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'require_image_start', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'require_image_always', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'allow_multifile', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'allow_op_multifile', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_fgsfds', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_honeypot', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'only_thread_duplicates', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'only_op_duplicates', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'board_title', 'Nelliel-powered image board', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'board_favicon', '', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'board_banner', '', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'language', 'en-US', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'thread_delay', '120', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'reply_delay', '60', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'abbreviate_thread', '5', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_post_files', '3', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_files_row', '3', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_multi_width', '175', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_multi_height', '175', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'jpeg_quality', '90', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'png_compression', '6', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_width', '256', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_height', '256', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_filesize', '4096', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_name_length', '100', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_email_length', '100', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_subject_length', '100', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_comment_length', '5000', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_comment_lines', '60', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'comment_display_lines', '15', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_source_length', '255', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_license_length', '255', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'threads_per_page', '10', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'page_limit', '10', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'page_buffer', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_posts', '1000', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_bumps', '1000', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'tripkey_marker', '!', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'date_format', 'Y/m/d (D) H:i:s', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'old_threads', 'ARCHIVE', 1, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'fgsfds_name', 'FGSFDS', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'indent_marker', '>>', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'file_sha256', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'file_sha512', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'enable_dynamic_pages', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'template_id', 'nelliel-template', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'filetype_icon_set_id', 'filetype-nelliel-basic', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'timestamp_filename', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_captcha', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_recaptcha', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'recaptcha_type', 'CHECKBOX', 1, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'poster_id_length', '6', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'display_render_timer', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'no_comment_text', '(no comment)', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'graphics', '1', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'jpeg', '1', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'gif', '1', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'png', '1', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'jpeg2000', '1', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'tiff', '1', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'bmp', '1', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'icon', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'photoshop', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'tga', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'pict', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'art', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'cel', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'kcf', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'ani', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'icns', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'illustrator', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'postscript', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'graphics', 'boolean', 'eps', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'audio', 'boolean', 'audio', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'audio', 'boolean', 'wave', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'audio', 'boolean', 'aiff', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'audio', 'boolean', 'mp3', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'audio', 'boolean', 'm4a', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'audio', 'boolean', 'flac', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'audio', 'boolean', 'aac', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'audio', 'boolean', 'ogg-audio', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'audio', 'boolean', 'au', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'audio', 'boolean', 'wma', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'audio', 'boolean', 'midi', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'audio', 'boolean', 'ac3', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'video', 'boolean', 'video', '1', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'video', 'boolean', 'mpeg', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'video', 'boolean', 'quicktime', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'video', 'boolean', 'avi', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'video', 'boolean', 'wmv', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'video', 'boolean', 'mpeg4', '1', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'video', 'boolean', 'mkv', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'video', 'boolean', 'flv', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'video', 'boolean', 'webm', '1', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'video', 'boolean', '3gp', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'video', 'boolean', 'ogg-video', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'video', 'boolean', 'm4v', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'document', 'boolean', 'document', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'document', 'boolean', 'rtf', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'document', 'boolean', 'pdf', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'document', 'boolean', 'msword', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'document', 'boolean', 'powerpoint', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'document', 'boolean', 'msexcel', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'document', 'boolean', 'txt', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'archive', 'boolean', 'archive', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'archive', 'boolean', 'gzip', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'archive', 'boolean', 'bzip2', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'archive', 'boolean', 'binhex', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'archive', 'boolean', 'lzh', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'archive', 'boolean', 'zip', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'archive', 'boolean', 'rar', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'archive', 'boolean', 'stuffit', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'archive', 'boolean', 'tar', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'archive', 'boolean', '7z', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'archive', 'boolean', 'iso', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'archive', 'boolean', 'dmg', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'other', 'boolean', 'other', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'other', 'boolean', 'swf', '0', 0, 0]);
        $this->insertDefaultRow(['filetype_enable', 'nelliel', 'other', 'boolean', 'blorb', '0', 0, 0]);
    }
}