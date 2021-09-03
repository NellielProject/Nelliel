<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableBoardDefaults extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_BOARD_DEFAULTS_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'setting_name' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'setting_value' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'edit_lock' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'setting_name' => ['row_check' => true, 'auto_inc' => false],
            'setting_value' => ['row_check' => false, 'auto_inc' => false],
            'edit_lock' => ['row_check' => false, 'auto_inc' => false]];
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

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        // General
        $this->insertDefaultRow(['name', '', 0]);
        $this->insertDefaultRow(['show_name', '1', 0]);
        $this->insertDefaultRow(['description', '', 0]);
        $this->insertDefaultRow(['show_description', '1', 0]);
        $this->insertDefaultRow(['favicon', '', 0]);
        $this->insertDefaultRow(['show_favicon', '0', 0]);
        $this->insertDefaultRow(['banner', '', 0]);
        $this->insertDefaultRow(['show_banner', '0', 0]);
        $this->insertDefaultRow(['locale', 'en_US', 0]);
        $this->insertDefaultRow(['exclude_from_overboards', '0', 0]);
        $this->insertDefaultRow(['user_delete_own', '1', 0]);
        $this->insertDefaultRow(['delete_post_renzoku', '0', 0]);
        $this->insertDefaultRow(['safety_level', 'SFW', 0]);
        $this->insertDefaultRow(['enabled_styles', '["style-nelliel","style-nelliel-2","style-nelliel-classic","style-burichan","style-futaba","style-nigra"]', 0]);

        // New post
        $this->insertDefaultRow(['enable_name_field', '1', 0]);
        $this->insertDefaultRow(['require_name', '0', 0]);
        $this->insertDefaultRow(['min_name_length', '0', 0]);
        $this->insertDefaultRow(['max_name_length', '100', 0]);
        $this->insertDefaultRow(['name_field_placeholder', '', 0]);
        $this->insertDefaultRow(['enable_email_field', '1', 0]);
        $this->insertDefaultRow(['require_email', '0', 0]);
        $this->insertDefaultRow(['min_email_length', '0', 0]);
        $this->insertDefaultRow(['max_email_length', '100', 0]);
        $this->insertDefaultRow(['email_field_placeholder', '', 0]);
        $this->insertDefaultRow(['enable_subject_field', '1', 0]);
        $this->insertDefaultRow(['require_subject', '0', 0]);
        $this->insertDefaultRow(['min_subject_length', '0', 0]);
        $this->insertDefaultRow(['max_subject_length', '200', 0]);
        $this->insertDefaultRow(['subject_field_placeholder', '', 0]);
        $this->insertDefaultRow(['enable_comment_field', '1', 0]);
        $this->insertDefaultRow(['require_comment', '0', 0]);
        $this->insertDefaultRow(['min_comment_length', '0', 0]);
        $this->insertDefaultRow(['max_comment_length', '5000', 0]);
        $this->insertDefaultRow(['comment_field_placeholder', '', 0]);
        $this->insertDefaultRow(['enable_fgsfds_field', '1', 0]);
        $this->insertDefaultRow(['fgsfds_field_placeholder', 'Enter commands here', 0]);
        $this->insertDefaultRow(['enable_password_field', '1', 0]);
        $this->insertDefaultRow(['password_field_placeholder', '', 0]);
        $this->insertDefaultRow(['truncate_long_fields', '0', 0]);
        $this->insertDefaultRow(['forced_anonymous', '0', 0]);

        $this->insertDefaultRow(['allow_op_files', '1', 0]);
        $this->insertDefaultRow(['require_op_file', '1', 0]);
        $this->insertDefaultRow(['allow_op_embeds', '0', 0]);
        $this->insertDefaultRow(['require_op_embed', '0', 0]);
        $this->insertDefaultRow(['max_op_files', '1', 0]);
        $this->insertDefaultRow(['max_op_embeds', '1', 0]);
        $this->insertDefaultRow(['max_op_total_uploads', '1', 0]);
        $this->insertDefaultRow(['allow_reply_files', '1', 0]);
        $this->insertDefaultRow(['require_reply_file', '0', 0]);
        $this->insertDefaultRow(['allow_reply_embeds', '0', 0]);
        $this->insertDefaultRow(['require_reply_embed', '0', 0]);
        $this->insertDefaultRow(['max_reply_files', '1', 0]);
        $this->insertDefaultRow(['max_reply_embeds', '1', 0]);
        $this->insertDefaultRow(['max_reply_total_uploads', '1', 0]);
        $this->insertDefaultRow(['check_board_file_duplicates', '0', 0]);
        $this->insertDefaultRow(['check_op_file_duplicates', '1', 0]);
        $this->insertDefaultRow(['check_thread_file_duplicates', '1', 0]);
        $this->insertDefaultRow(['check_board_embed_duplicates', '0', 0]);
        $this->insertDefaultRow(['check_op_embed_duplicates', '1', 0]);
        $this->insertDefaultRow(['check_thread_embed_duplicates', '1', 0]);
        $this->insertDefaultRow(['embed_replaces_file', '1', 0]);
        $this->insertDefaultRow(['limit_thread_uploads', '1', 0]);
        $this->insertDefaultRow(['max_thread_uploads', '1000', 0]);
        $this->insertDefaultRow(['max_filesize', '5120', 0]);
        $this->insertDefaultRow(['enable_spoilers', '1', 0]);

        $this->insertDefaultRow(['allow_sage', '1', 0]);
        $this->insertDefaultRow(['allow_tripcodes', '1', 0]);
        $this->insertDefaultRow(['process_new_post_commands', '1', 0]);
        $this->insertDefaultRow(['allow_email_commands', '1', 0]);
        $this->insertDefaultRow(['thread_renzoku', '120', 0]);
        $this->insertDefaultRow(['reply_renzoku', '30', 0]);
        $this->insertDefaultRow(['max_cites', '30', 0]);
        $this->insertDefaultRow(['max_crossboard_cites', '15', 0]);
        $this->insertDefaultRow(['max_comment_urls', '15', 0]);
        $this->insertDefaultRow(['trim_comment_newlines_start', '0', 0]);
        $this->insertDefaultRow(['trim_comment_newlines_end', '1', 0]);
        $this->insertDefaultRow(['use_anonymous_names', '1', 0]);
        $this->insertDefaultRow(['anonymous_names', '["Anonymous"]', 0]);
        $this->insertDefaultRow(['always_noko', '0', 0]);

        // Content handling
        $this->insertDefaultRow(['preferred_filename', 'timestamp', 0]);
        $this->insertDefaultRow(['generate_preview', '1', 0]);
        $this->insertDefaultRow(['max_preview_width', '250', 0]);
        $this->insertDefaultRow(['max_preview_height', '250', 0]);
        $this->insertDefaultRow(['jpeg_quality', '85', 0]);
        $this->insertDefaultRow(['use_png_preview', '0', 0]);
        $this->insertDefaultRow(['png_compression', '6', 0]);
        $this->insertDefaultRow(['animated_preview', '0', 0]);
        $this->insertDefaultRow(['deleted_upload_placeholder', '0', 0]);
        $this->insertDefaultRow(['limit_post_count', '1', 0]);
        $this->insertDefaultRow(['max_posts', '1000', 0]);
        $this->insertDefaultRow(['limit_bump_count', '1', 0]);
        $this->insertDefaultRow(['max_bumps', '1000', 0]);
        $this->insertDefaultRow(['active_threads', '100', 0]);
        $this->insertDefaultRow(['thread_buffer', '100', 0]);
        $this->insertDefaultRow(['old_threads', 'PRUNE', 0]);
        $this->insertDefaultRow(['max_archive_threads', '500', 0]);
        $this->insertDefaultRow(['do_archive_pruning', '1', 0]);
        $this->insertDefaultRow(['store_exif_data', '1', 0]);
        $this->insertDefaultRow(['max_slug_length', '80', 0]);
        $this->insertDefaultRow(['slugify_thread_url', '0', 0]);

        // Page rendering
        $this->insertDefaultRow(['date_format', 'Y/m/d (D) H:i:s', 0]);
        $this->insertDefaultRow(['index_thread_replies', '5', 0]);
        $this->insertDefaultRow(['index_sticky_replies', '1', 0]);
        $this->insertDefaultRow(['threads_per_page', '10', 0]);
        $this->insertDefaultRow(['max_index_comment_lines', '15', 0]);
        $this->insertDefaultRow(['filter_zalgo', '0', 0]);
        $this->insertDefaultRow(['display_render_timer', '1', 0]);
        $this->insertDefaultRow(['enable_dynamic_pages', '0', 0]);
        $this->insertDefaultRow(['generate_catalog', '1', 0]);
        $this->insertDefaultRow(['template_id', 'template-nelliel-basic', 0]);
        $this->insertDefaultRow(['default_style', 'style-nelliel', 0]);
        $this->insertDefaultRow(['ui_icon_set', 'icons-nelliel-basic', 0]);
        $this->insertDefaultRow(['filetype_icon_set', 'icons-nelliel-basic', 0]);
        $this->insertDefaultRow(['icon_set_fallback', '1', 0]);
        $this->insertDefaultRow(['use_file_icon', '1', 0]);
        $this->insertDefaultRow(['max_uploads_row', '3', 0]);
        $this->insertDefaultRow(['max_preview_display_width', '250', 0]);
        $this->insertDefaultRow(['max_preview_display_height', '250', 0]);
        $this->insertDefaultRow(['max_embed_display_width', '300', 0]);
        $this->insertDefaultRow(['max_embed_display_height', '300', 0]);
        $this->insertDefaultRow(['max_multi_display_width', '200', 0]);
        $this->insertDefaultRow(['max_multi_display_height', '200', 0]);
        $this->insertDefaultRow(['max_catalog_display_width', '120', 0]);
        $this->insertDefaultRow(['max_catalog_display_height', '120', 0]);
        $this->insertDefaultRow(['filename_display_length', '25', 0]);
        $this->insertDefaultRow(['embed_url_display_length', '25', 0]);
        $this->insertDefaultRow(['fgsfds_name', 'FGSFDS', 0]);
        $this->insertDefaultRow(['indent_marker', '>>', 0]);
        $this->insertDefaultRow(['tripcode_marker', '!', 0]);
        $this->insertDefaultRow(['no_comment_text', '(no comment)', 0]);
        $this->insertDefaultRow(['display_post_backlinks', '1', 0]);
        $this->insertDefaultRow(['display_poster_id', '0', 0]);
        $this->insertDefaultRow(['poster_id_colors', '0', 0]);
        $this->insertDefaultRow(['poster_id_length', '6', 0]);
        $this->insertDefaultRow(['create_url_links', '1', 0]);
        $this->insertDefaultRow(['url_protocols', 'http|https|ftp|sftp|irc|nntp', 0]);
        $this->insertDefaultRow(['url_prefix', '', 0]);
        $this->insertDefaultRow(['display_original_name', '1', 0]);
        $this->insertDefaultRow(['board_content_disclaimer', '', 0]);
        $this->insertDefaultRow(['index_nav_top', '0', 0]);
        $this->insertDefaultRow(['index_nav_bottom', '1', 0]);
        $this->insertDefaultRow(['image_spoiler_cover', 'media/core/covers/spoiler.png', 0]);
        $this->insertDefaultRow(['image_deleted_file', 'media/core/placeholders/deleted_file.png', 0]);
        $this->insertDefaultRow(['subject_in_title', '1', 0]);
        $this->insertDefaultRow(['slug_in_title', '1', 0]);
        $this->insertDefaultRow(['thread_number_in_title', '1', 0]);
        $this->insertDefaultRow(['prefix_board_title', '1', 0]);
        $this->insertDefaultRow(['list_file_formats', '0', 0]);
        $this->insertDefaultRow(['list_file_extensions', '1', 0]);

        // Anti-spam
        $this->insertDefaultRow(['use_post_captcha', '0', 0]);
        $this->insertDefaultRow(['use_post_recaptcha', '0', 0]);
        $this->insertDefaultRow(['use_report_captcha', '0', 0]);
        $this->insertDefaultRow(['use_report_recaptcha', '0', 0]);
        $this->insertDefaultRow(['use_honeypot', '1', 0]);

        // Filetypes
        $this->insertDefaultRow(['enabled_filetypes', '{"graphics": {"enabled": true, "formats": ["jpeg", "gif", "png", "webp"]}, "video": {"enabled": true, "formats": ["mpeg4", "webm"]}}', 0]);
    }
}