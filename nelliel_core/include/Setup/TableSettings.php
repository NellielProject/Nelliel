<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableSettings extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_SETTINGS_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'setting_category' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'setting_owner' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'data_type' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'setting_name' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'setting_options' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'default_value' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'setting_label' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'setting_description' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'input_attributes' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry               " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            setting_category    VARCHAR(50) NOT NULL,
            setting_owner       VARCHAR(50) NOT NULL,
            data_type           VARCHAR(50) NOT NULL,
            setting_name        VARCHAR(50) NOT NULL,
            setting_options     TEXT NOT NULL,
            default_value       TEXT NOT NULL,
            setting_label       TEXT NOT NULL,
            setting_description TEXT NOT NULL,
            input_attributes    TEXT NOT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'home_page', '', '/', 'Site home page', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'post_password_algorithm', '', 'sha256', 'Post password hash algorithm', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'secure_tripcode_algorithm', '', 'sha256', 'Secure tripcode hash algorithm', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'do_password_rehash', '', '0', 'Rehash account passwords', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'index_filename_format', '', 'index-%d', 'Thread filename (sprintf)', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'thread_filename_format', '', 'thread-%d', 'Index filename (sprintf)', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'template_id', '', 'template-nelliel-basic', 'ID of default template for site', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'locale', '', 'en_US', 'Locale for site (use ISO language + country code)', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'name', '', '', 'Site name', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'slogan', '', '', 'Site slogan', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'favicon', '', '', 'Site favicon', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'banner', '', '', 'Site banner', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'recaptcha_site_key', '', '', 'reCAPTCHA site key', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'recaptcha_sekrit_key', '', '', 'reCAPTCHA secret key', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'recaptcha_type', '{"CHECKBOX":{"label":"Checkbox"}}', 'CHECKBOX', 'reCAPTCHA type', '', '{"type":"radio"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'captcha_timeout', '', '1800', 'CAPTCHA timeout (seconds)', '', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'captcha_rate_limit', '', '12', 'CAPTCHA requests per IP in one minute (0 to disable check)', '', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'captcha_width', '', '250', 'Width of CAPTCHA image', '', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'captcha_height', '', '80', 'Height of CAPTCHA image', '', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'captcha_character_count', '', '5', 'Number of characters in CAPTCHA', '', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'use_login_captcha', '', '0', 'Use CAPTCHA for login', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'use_login_recaptcha', '', '0', 'Use reCAPTCHA for login', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'use_register_captcha', '', '0', 'Use CAPTCHA for registration', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'use_register_recaptcha', '', '0', 'Use reCAPTCHA for registration', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'graphics_handler', '{"GD":{"label":"GD"}, "ImageMagick":{"label":"ImageMagick"}, "GraphicsMagick":{"label":"GraphicsMagick"}}', 'GD', 'Preferred graphics handler', '', '{"type":"radio"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'overboard_active', '', '0', 'Enable overboard', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'overboard_uri', '', 'overboard', 'Overboard URI', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'overboard_threads', '', '20', 'Max threads on overboard', '', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'sfw_overboard_active', '', '0', 'Enable SFW overboard', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'sfw_overboard_uri', '', 'sfwoverboard', 'SFW overboard URI', '', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'sfw_overboard_threads', '', '20', 'Max threads on SFW overboard', '', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'user_board_creation', '', '0', 'Allow users to create boards', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'max_boards_per_user', '', '1', 'Max boards per user', '', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'display_render_timer', '', '1', 'Display rendering timer', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'login_delay', '', '3', 'Delay between login attempts', '', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'session_length', '', '10800', 'Session timeout (seconds)', '', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'only_alphanumeric_board_ids', '', '1', 'Allow only alphanumeric board IDs', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_tripkeys', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'force_anonymous', '', '0', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_name', '', '1', 'Display board name', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_slogan', '', '1', 'Display board slogan', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_favicon', '', '0', 'Show favicon', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_banner', '', '0', 'Display banner', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_preview', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_file_icon', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_png_preview', '', '0', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'animated_gif_preview', '', '0', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_content_start', '', '1', 'Require an image, file or embed to start new thread', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_content_always', '', '0', 'Require an image, file or embed for all posts', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_multifile', '', '0', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_op_multifile', '', '0', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_fgsfds', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_honeypot', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'only_thread_duplicates', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'only_op_duplicates', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'name', '', '', 'Board name', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'slogan', '', '', 'Board slogan', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'description', '', '', 'Board description', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'favicon', '', '', 'Board favicon', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'banner', '', '', 'Board banner', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'locale', '', 'en_US', 'Locale for the board (use ISO language + country code)', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'thread_cooldown', '', '120', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'reply_cooldown', '', '60', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'abbreviate_thread', '', '5', 'Max posts in a thread before abbreviating on index page', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_post_files', '', '3', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_files_row', '', '3', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_multi_width', '', '175', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_multi_height', '', '175', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'jpeg_quality', '', '90', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'png_compression', '', '6', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_width', '', '256', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_height', '', '256', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_filesize', '', '4096', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_name_length', '', '100', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_email_length', '', '100', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_subject_length', '', '100', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_comment_length', '', '5000', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'comment_display_lines', '', '15', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'truncate_long_fields', '', '0', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'threads_per_page', '', '10', 'Threads per page', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'page_limit', '', '10', 'Max pages in index', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'thread_buffer', '', '100', 'Max threads in buffer', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_posts', '', '1000', 'Max posts per thread', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_bumps', '', '1000', 'Max bumps per thread', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'tripkey_marker', '', '!', '', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'date_format', '', 'Y/m/d (D) H:i:s', 'Format for post time (uses PHP date() function)', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'old_threads', '{"NOTHING":{"label":"Nothing"}, "PRUNE":{"label":"Prune"}, "ARCHIVE":{"label":"Archive"}}', 'ARCHIVE', '', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'do_archive_pruning', '', '1', 'Prune oldest threads in archive when limit is reached', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_archive_threads', '', '500', 'Max threads kept in archive', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'fgsfds_name', '', 'FGSFDS', 'Display of FGSFDS field', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'indent_marker', '', '>>', '', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'file_sha256', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'file_sha512', '', '0', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_dynamic_pages', '', '0', 'Allow visitors to use dynamic page rendering', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'template_id', '', 'template-nelliel-basic', 'ID of template for board to use', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'icon_set_id', '', 'icons-nelliel-basic', 'ID of icon set for board to use', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'preferred_filename', '', 'original', '', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'display_original_name', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_post_captcha', '', '0', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_post_recaptcha', '', '0', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_report_captcha', '', '0', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_report_recaptcha', '', '0', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'poster_id_colors', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'display_render_timer', '', '1', 'Display the rendering timer', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'no_comment_text', '', '(no comment)', '', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'display_post_backlinks', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'display_poster_id', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'generate_catalog', '', '1', 'Generate catalog for this board', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_catalog_width', '', '128', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_catalog_height', '', '128', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_spoilers', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'safety_level', '', 'SFW', '', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_cite_links', '', '40', '', '', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'filter_combining_characters', '', '0', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'create_url_links', '', '1', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'url_protocols', '', 'http|https|ftp|sftp|irc|nntp', '', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'url_prefix', '', '', '', '', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'exclude_from_overboards', '', '0', '', '', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'enabled_filetypes', '', '{"graphics":{"enabled":true,"formats":{"jpeg":{"enabled":true},"gif":{"enabled":true},"png":{"enabled":true},"webp":{"enabled":true}}}}', '', '', '{"type":"text"}']);
    }
}