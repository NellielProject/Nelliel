<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableBoardConfig extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = '_config';
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'setting_name' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'setting_value' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'edit_lock' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            setting_name    VARCHAR(50) NOT NULL UNIQUE,
            setting_value   TEXT NOT NULL,
            edit_lock       SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['allow_tripkeys', '1', 0]);
        $this->insertDefaultRow(['forced_anonymous', '0', 0]);
        $this->insertDefaultRow(['name', '', 0]);
        $this->insertDefaultRow(['slogan', '', 0]);
        $this->insertDefaultRow(['description', '', 0]);
        $this->insertDefaultRow(['favicon', '', 0]);
        $this->insertDefaultRow(['banner', '', 0]);
        $this->insertDefaultRow(['show_name', '1', 0]);
        $this->insertDefaultRow(['show_slogan', '1', 0]);
        $this->insertDefaultRow(['show_favicon', '0', 0]);
        $this->insertDefaultRow(['show_banner', '0', 0]);
        $this->insertDefaultRow(['use_preview', '1', 0]);
        $this->insertDefaultRow(['use_file_icon', '1', 0]);
        $this->insertDefaultRow(['use_png_preview', '0', 0]);
        $this->insertDefaultRow(['animated_gif_preview', '0', 0]);
        $this->insertDefaultRow(['require_content_start', '1', 0]);
        $this->insertDefaultRow(['require_content_always', '0', 0]);
        $this->insertDefaultRow(['allow_multifile', '0', 0]);
        $this->insertDefaultRow(['allow_op_multifile', '0', 0]);
        $this->insertDefaultRow(['use_fgsfds', '1', 0]);
        $this->insertDefaultRow(['use_honeypot', '1', 0]);
        $this->insertDefaultRow(['check_thread_duplicates', '1', 0]);
        $this->insertDefaultRow(['check_op_duplicates', '1', 0]);
        $this->insertDefaultRow(['locale', 'en_US', 0]);
        $this->insertDefaultRow(['thread_cooldown', '120', 0]);
        $this->insertDefaultRow(['reply_cooldown', '60', 0]);
        $this->insertDefaultRow(['abbreviate_thread', '5', 0]);
        $this->insertDefaultRow(['max_post_files', '3', 0]);
        $this->insertDefaultRow(['max_files_row', '3', 0]);
        $this->insertDefaultRow(['max_multi_width', '175', 0]);
        $this->insertDefaultRow(['max_multi_height', '175', 0]);
        $this->insertDefaultRow(['jpeg_quality', '90', 0]);
        $this->insertDefaultRow(['png_compression', '6', 0]);
        $this->insertDefaultRow(['max_width', '256', 0]);
        $this->insertDefaultRow(['max_height', '256', 0]);
        $this->insertDefaultRow(['max_filesize', '4096', 0]);
        $this->insertDefaultRow(['max_name_length', '100', 0]);
        $this->insertDefaultRow(['max_email_length', '100', 0]);
        $this->insertDefaultRow(['max_subject_length', '100', 0]);
        $this->insertDefaultRow(['max_comment_length', '5000', 0]);
        $this->insertDefaultRow(['comment_display_lines', '15', 0]);
        $this->insertDefaultRow(['truncate_long_fields', '0', 0]);
        $this->insertDefaultRow(['threads_per_page', '10', 0]);
        $this->insertDefaultRow(['page_limit', '10', 0]);
        $this->insertDefaultRow(['thread_buffer', '100', 0]);
        $this->insertDefaultRow(['max_posts', '1000', 0]);
        $this->insertDefaultRow(['max_bumps', '1000', 0]);
        $this->insertDefaultRow(['tripkey_marker', '!', 0]);
        $this->insertDefaultRow(['date_format', 'Y/m/d (D) H:i:s', 0]);
        $this->insertDefaultRow(['old_threads', 'ARCHIVE', 0]);
        $this->insertDefaultRow(['do_archive_pruning', '1', 0]);
        $this->insertDefaultRow(['max_archive_threads', '500', 0]);
        $this->insertDefaultRow(['fgsfds_name', 'FGSFDS', 0]);
        $this->insertDefaultRow(['indent_marker', '>>', 0]);
        $this->insertDefaultRow(['file_sha256', '1', 0]);
        $this->insertDefaultRow(['file_sha512', '0', 0]);
        $this->insertDefaultRow(['enable_dynamic_pages', '0', 0]);
        $this->insertDefaultRow(['template_id', 'template-nelliel-basic', 0]);
        $this->insertDefaultRow(['icon_set_id', 'icons-nelliel-basic', 0]);
        $this->insertDefaultRow(['preferred_filename', 'original', 0]);
        $this->insertDefaultRow(['display_original_name', '1', 0]);
        $this->insertDefaultRow(['use_post_captcha', '0', 0]);
        $this->insertDefaultRow(['use_post_recaptcha', '0', 0]);
        $this->insertDefaultRow(['use_report_captcha', '0', 0]);
        $this->insertDefaultRow(['use_report_recaptcha', '0', 0]);
        $this->insertDefaultRow(['poster_id_colors', '1', 0]);
        $this->insertDefaultRow(['display_render_timer', '1', 0]);
        $this->insertDefaultRow(['no_comment_text', '(no comment)', 0]);
        $this->insertDefaultRow(['display_post_backlinks', '1', 0]);
        $this->insertDefaultRow(['display_poster_id', '1', 0]);
        $this->insertDefaultRow(['generate_catalog', '1', 0]);
        $this->insertDefaultRow(['max_catalog_width', '128', 0]);
        $this->insertDefaultRow(['max_catalog_height', '128', 0]);
        $this->insertDefaultRow(['enable_spoilers', '1', 0]);
        $this->insertDefaultRow(['safety_level', 'SFW', 0]);
        $this->insertDefaultRow(['max_cite_links', '40', 0]);
        $this->insertDefaultRow(['filter_combining_characters', '0', 0]);
        $this->insertDefaultRow(['create_url_links', '1', 0]);
        $this->insertDefaultRow(['url_protocols', 'http|https|ftp|sftp|irc|nntp', 0]);
        $this->insertDefaultRow(['url_prefix', '', 0]);
        $this->insertDefaultRow(['exclude_from_overboards', '0', 0]);
        $this->insertDefaultRow(['enabled_filetypes', '{"graphics":{"enabled":true,"formats":{"jpeg":{"enabled":true},"gif":{"enabled":true},"png":{"enabled":true},"webp":{"enabled":true}}}}', 0]);
        $this->insertDefaultRow(['user_delete_own', '1', 0]);
        $this->insertDefaultRow(['deleted_content_placeholder', '0', 0]);
    }
}