<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableBoardConfig extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = '_config';
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'config_type' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'config_owner' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'config_category' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'data_type' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'config_name' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'setting' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'select_type' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'edit_lock' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry               " . $auto_inc[0] . " NOT NULL " . $auto_inc[1] . " PRIMARY KEY,
            config_type         VARCHAR(50) NOT NULL,
            config_owner        VARCHAR(50) NOT NULL,
            config_category     VARCHAR(50) NOT NULL,
            data_type           VARCHAR(50) NOT NULL,
            config_name         VARCHAR(100) NOT NULL,
            setting             TEXT NOT NULL,
            select_type         SMALLINT NOT NULL DEFAULT 0,
            edit_lock           SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'allow_tripkeys', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'force_anonymous', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'show_name', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'show_slogan', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'show_favicon', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'show_banner', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_preview', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_file_icon', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_png_preview', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'animated_gif_preview', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'require_content_start', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'require_content_always', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'allow_multifile', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'allow_op_multifile', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_fgsfds', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_honeypot', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'only_thread_duplicates', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'only_op_duplicates', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'name', '', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'slogan', '', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'description', '', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'favicon', '', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'banner', '', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'locale', 'en_US', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'thread_cooldown', '120', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'reply_cooldown', '60', 0, 0]);
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
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'comment_display_lines', '15', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'truncate_long_fields', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'threads_per_page', '10', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'page_limit', '10', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'thread_buffer', '100', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_posts', '1000', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_bumps', '1000', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'tripkey_marker', '!', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'date_format', 'Y/m/d (D) H:i:s', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'old_threads', 'ARCHIVE', 1, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'do_archive_pruning', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_archive_threads', '500', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'fgsfds_name', 'FGSFDS', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'indent_marker', '>>', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'file_sha256', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'file_sha512', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'enable_dynamic_pages', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'template_id', 'template-nelliel-basic', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'filetype_icon_set_id', 'filetype-nelliel-basic', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'preferred_filename', 'original', 1, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'display_original_name', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_post_captcha', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_post_recaptcha', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_report_captcha', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'use_report_recaptcha', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'poster_id_colors', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'display_render_timer', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'no_comment_text', '(no comment)', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'display_post_backlinks', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'display_poster_id', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'generate_catalog', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_catalog_width', '128', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_catalog_height', '128', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'enable_spoilers', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'safety_level', 'SFW', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'integer', 'max_cite_links', '40', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'filter_combining_characters', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'create_url_links', '1', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'url_protocols', 'http|https|ftp|sftp|irc|nntp', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'url_prefix', '', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'boolean', 'exclude_from_overboards', '0', 0, 0]);
        $this->insertDefaultRow(['board_setting', 'nelliel', 'general', 'string', 'enabled_filetypes', '{"graphics":{"enabled":true,"formats":{"jpeg":{"enabled":true},"gif":{"enabled":true},"png":{"enabled":true},"webp":{"enabled":true}}}}', 0, 0]);
    }
}