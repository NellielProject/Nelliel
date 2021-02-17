<?php

declare(strict_types=1);


namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableSettings extends Table
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
            'setting_description' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'input_attributes' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
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
            setting_description       TEXT NOT NULL,
            input_attributes    TEXT NOT NULL,
            moar                TEXT DEFAULT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        // Site
        // General
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'name', '', '', 'Site name', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'show_name', '', '1', 'Display site name in header', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'description', '', '', 'Site description', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'show_description', '', '1', 'Display site description in header', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'favicon', '', '', 'Site favicon', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'show_favicon', '', '0', 'Show site favicon', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'banner', '', '', 'Site banner', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'show_banner', '', '0', 'Display site banner', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'home_page', '', '/', 'Site home page', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'locale', '', 'en_US', 'Locale for site (use ISO language + country code)', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'only_alphanumeric_board_ids', '', '1', 'Allow only alphanumeric board IDs', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'max_report_items', '', '5', 'Maximum items that can be reported at one time', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'max_delete_items', '', '5', 'Maximum items that can be deleted at one time', '{"type":"number"}']);

        // Bans
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'must_see_ban', '', '1', 'Bans must be seen at least once before expiration purge', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'allow_ban_appeals', '', '1', 'Allow ban appeals', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'min_time_before_ban_appeal', '', '3600', 'Minimum time before a ban can be appealed (seconds)', '{"type":"number"}']);

        // Posts and rendering
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'index_filename_format', '', 'index%d', 'Index filename (sprintf)', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'thread_filename_format', '', '%d', 'Thread filename (sprintf)', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'template_id', '', 'template-nelliel-basic', 'ID of default template for site', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'graphics_handler', '{"GD":{"label":"GD"}, "ImageMagick":{"label":"ImageMagick"}, "GraphicsMagick":{"label":"GraphicsMagick"}}', 'GD', 'Preferred graphics handler', '{"type":"select"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'noreferrer_nofollow', '', '', 'Add noreferrer and nofollow to external links in posts', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'display_render_timer', '', '1', 'Display rendering timer', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'site_content_disclaimer', '', '', 'Site-wide disclaimer added to the bottom of posts', '{"type":"text"}']);

        // Hashing and security
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'post_password_algorithm', '', 'sha256', 'Post password hash algorithm', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'secure_tripcode_algorithm', '', 'sha256', 'Secure tripcode hash algorithm', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'do_password_rehash', '', '0', 'Rehash account passwords', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'login_delay', '', '3', 'Delay between login attempts', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'session_length', '', '10800', 'Session timeout (seconds)', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'store_unhashed_ip', '', '1', 'Store unhashed IP addresses; (hashed IP will always be stored)', '{"type":"checkbox"}']);

        // CAPTCHA
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'captcha_width', '', '250', 'Width of CAPTCHA image', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'captcha_height', '', '80', 'Height of CAPTCHA image', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'captcha_character_count', '', '5', 'Number of characters in CAPTCHA', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'captcha_timeout', '', '1800', 'CAPTCHA timeout (seconds)', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'captcha_rate_limit', '', '12', 'CAPTCHA requests per IP in one minute (0 to disable check)', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'recaptcha_site_key', '', '', 'reCAPTCHA site key', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'recaptcha_sekrit_key', '', '', 'reCAPTCHA secret key', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'recaptcha_type', '{"CHECKBOX":{"label":"Checkbox"}}', 'CHECKBOX', 'reCAPTCHA type', '{"type":"select"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'use_login_captcha', '', '0', 'Use CAPTCHA for login', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'use_login_recaptcha', '', '0', 'Use reCAPTCHA for login', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'use_register_captcha', '', '0', 'Use CAPTCHA for registration', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'use_register_recaptcha', '', '0', 'Use reCAPTCHA for registration', '{"type":"checkbox"}']);

        // Overboard
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'overboard_active', '', '0', 'Enable overboard', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'overboard_uri', '', 'overboard', 'Overboard URI', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'overboard_threads', '', '20', 'Maximum threads on overboard', '{"type":"number"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'nsfl_on_overboard', '', '0', 'Include NSFL content on overboard', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'boolean', 'sfw_overboard_active', '', '0', 'Enable SFW overboard', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'string', 'sfw_overboard_uri', '', 'sfwoverboard', 'SFW overboard URI', '{"type":"text"}']);
        $this->insertDefaultRow(['core', 'nelliel', 'integer', 'sfw_overboard_threads', '', '20', 'Maximum threads on SFW overboard', '{"type":"number"}']);

        // Board
        // General
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'name', '', '', 'Board name', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_name', '', '1', 'Display board name in header', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'description', '', '', 'Board description', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_description', '', '1', 'Display board description in header', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'favicon', '', '', 'Board favicon', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_favicon', '', '0', 'Show board favicon', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'banner', '', '', 'Board banner', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_banner', '', '0', 'Display board banner', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'locale', '', 'en_US', 'Locale for the board (use ISO language + country code)', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'exclude_from_overboards', '', '0', 'Exclude threads on this board from the overboards', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'user_delete_own', '', '1', 'Let users delete own posts and content', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'delete_post_cooldown', '', '0', 'Cooldown after posting before user can delete the post', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'safety_level', '{"SFW":{"label":"SFW - Safe For Work"}, "NSFW":{"label":"NSFW - Not Safe For Work"}, "NSFL":{"label":"NSFL - Not Safe For Life"}}', 'SFW', 'Content safety level of board', '{"type":"select"}']);

        // New post
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_files', '', '1', 'Allow users to upload files', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_embeds', '', '0', 'Allow users to post embedded content', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_op_uploads', '', '1', 'Allow files or embeds in OP', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_reply_uploads', '', '1', 'Allow files or embeds in replies', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_op_multiple', '', '0', 'Allow mutiple files or embeds in OP', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_reply_multiple', '', '0', 'Allow multiple files or embeds in replies', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'embed_replaces_file', '', '1', 'If an embed is given along with files, the files will be discarded', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_op_upload', '', '1', 'Require a file or embed to start new thread', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_reply_upload', '', '0', 'Require a file or embed for all posts', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'check_thread_duplicates', '', '1', 'Check for duplicates in current thread when replying', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'check_op_duplicates', '', '1', 'Check for duplicates in other op posts when creating new thread', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_spoilers', '', '1', 'Enable spoilers for uploads', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'limit_thread_uploads', '', '1', 'Limit the number of uploads in a thread', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_thread_uploads', '', '1000', 'Maximum number of uploads in a thread', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_op_comment', '', '0', 'Require a text comment when starting a thread', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_reply_comment', '', '0', 'Require a text comment when replying', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_name_length', '', '100', 'Maximum length of name', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_email_length', '', '100', 'Maximum length of email', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_subject_length', '', '100', 'Maximum length of subject', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_comment_length', '', '5000', 'Maximum length of comment', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_post_uploads', '', '3', 'Maximum number of uploads per post', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_filesize', '', '4096', 'Maximum file size (KB)', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'truncate_long_fields', '', '0', 'Truncate fields that are too long instead of giving an error', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'forced_anonymous', '', '0', 'Force anonymous posting', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_tripcodes', '', '1', 'Allow use of tripcodes', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_fgsfds', '', '1', 'Use FGSFDS field for commands (noko, sage, etc)', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'thread_renzoku', '', '120', 'Cooldown for new threads (seconds)', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'reply_renzoku', '', '20', 'Cooldown for new replies (seconds)', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_cites', '', '30', 'Maximum cite in a comment', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_crossboard_cites', '', '15', 'Maximum cross-board cites in a comment', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'trim_comment_newlines_start', '', '0', 'Trim extra newlines and whitespace at start of comment', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'trim_comment_newlines_end', '', '1', 'Trim extra new lines and whitespace at the end of comment', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'anonymous_names', '', '["Anonymous"]', 'Names that can be randomly chosen when a name is not provided for forced anonymous is on. Must be a JSON array.', '{"type":"text"}']);

        // Content handling
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'preferred_filename', '{"original":{"label":"Original"}, "timestamp":{"label":"Unix timestamp"}, "md5":{"label":"MD5"}, "sha1":{"label":"SHA1"}, "sha256":{"label":"SHA256"}, "sha512":{"label":"SHA512"}}', 'original', 'Preferred filename for uploads', '{"type":"select"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'generate_preview', '', '1', 'Generate previews', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_preview_width', '', '250', 'Maximum preview width', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_preview_height', '', '250', 'Maximum preview height', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'jpeg_quality', '', '90', 'JPEG quality (0-100)', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_png_preview', '', '0', 'Use PNG instead of JPEG for previews', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'png_compression', '', '6', 'PNG compression (0-9)', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'animated_gif_preview', '', '0', 'Used animated previews (requires extra libraries)', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'deleted_content_placeholder', '', '0', 'Leave a placeholder when deleting files/embeds', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'limit_post_count', '', '1', 'Limit the number of posts in a thread', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_posts', '', '1000', 'Maximum posts per thread', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'limit_bump_count', '', '1', 'Limit the number of bumps a thread can have', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_bumps', '', '1000', 'Maximum bumps per thread', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'active_threads', '', '100', 'Active threads (shown in index) before buffer', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'thread_buffer', '', '50', 'Maximum threads in buffer', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'old_threads', '{"NOTHING":{"label":"Nothing"}, "PRUNE":{"label":"Prune"}, "ARCHIVE":{"label":"Archive"}}', 'ARCHIVE', 'How to handle old threads', '{"type":"select"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_archive_threads', '', '1000', 'Maximum threads kept in archive', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'do_archive_pruning', '', '1', 'Prune oldest threads in archive when limit is reached', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'store_exif_data', '', '1', 'Store EXIF data from images (if present)', '{"type":"checkbox"}']);

        // Page rendering
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'date_format', '', 'Y/m/d (D) H:i:s', 'Format for post time (uses PHP date() function)', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'index_thread_replies', '', '5', 'How many replies to a thread should be displayed on the index page', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'index_sticky_replies', '', '1', 'How many replies to a stickied thread should be displayed on the index page', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'threads_per_page', '', '10', 'Threads per page', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'comment_display_lines', '', '15', 'How many lines of comment to display when abbreviated', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'filter_combining_characters', '', '0', 'Filter Unicode combining characters (zalgo text, etc). Warning: This can break text in some languages!', 'Combining characters are sometimes misused for things like Zalgo text. WARNING: This can break a bunch of non-English languages!', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'display_render_timer', '', '1', 'Display the rendering timer', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_dynamic_pages', '', '0', 'Allow visitors to use dynamic page rendering (Currently Unused)', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'generate_catalog', '', '1', 'Generate catalog for this board', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'template_id', '', 'template-nelliel-basic', 'ID of template for board to use', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'style_id', '', 'style-nelliel', 'ID of default style for board', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'icon_set_id', '', 'icons-nelliel-basic', 'ID of icon set for board to use', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_file_icon', '', '1', 'Use filetype icon for non-images', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_uploads_row', '', '3', 'Maximum number of uploads to display in each row', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_display_width', '', '250', 'Maximum display width for uploads', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_display_height', '', '250', 'Maximum display height for uploads', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_multi_display_width', '', '200', 'Maximum display width for multiple uploads', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_multi_display_height', '', '200', 'Maximum display height for multiple uploads', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_catalog_display_width', '', '120', 'Maximum display width for uploads in catalog', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_catalog_display_height', '', '120', 'Maximum display height for uploads in catalog', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'filename_display_length', '', '25', 'Maximum characters of filename to display', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'embed_url_display_length', '', '25', 'Maximum characters of embed URL to display', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'fgsfds_name', '', 'FGSFDS', 'Display name of FGSFDS field', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'indent_marker', '', '>>', 'Indent marker next to replies', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'tripcode_marker', '', '!', 'Tripcode marker', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'no_comment_text', '', '(no comment)', 'Text when there is no comment', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'display_post_backlinks', '', '1', 'Display backlinks for a post that has been referenced', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'display_poster_id', '', '0', 'Display ID for posters in a thread', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'poster_id_colors', '', '0', 'Use a color background for poster IDs', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'poster_id_length', '', '6', 'Characters for poster ID (limited by hash length)', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'create_url_links', '', '1', 'Convert URLs into links', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'url_protocols', '', 'http|https|ftp|sftp|irc|nntp', 'Protocols which will be parsed to links (must be separated with |)', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'url_prefix', '', '', 'Prefix that will be added to URLs', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_url_links', '', '15', 'Maximum URL links to generate', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'display_original_name', '', '1', 'Display the original file name', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'board_content_disclaimer', '', '', 'Disclaimer added to the bottom of posts on this board', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'index_nav_top', '', '1', 'Display index navigation at top of page', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'index_nav_bottom', '', '1', 'Display index navigation at bottom of page', '{"type":"checkbox"}']);

        // Anti-spam
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_post_captcha', '', '0', 'Use CAPTCHA for new posts and threads', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_post_recaptcha', '', '0', 'Use reCAPTCHA for new posts and threads', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_report_captcha', '', '0', 'Use CAPTCHA for making reports', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_report_recaptcha', '', '0', 'Use reCAPTCHA for making reports', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_honeypot', '', '1', 'Use honeypot for spambots', '{"type":"checkbox"}']);

        // Filetypes
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'enabled_filetypes', '', '{"graphics":{"enabled":true,"formats":{"jpeg":{"enabled":true},"gif":{"enabled":true},"png":{"enabled":true},"webp":{"enabled":true}}},"video":{"enabled":true,"formats":{"mpeg4":{"enabled":true},"webm":{"enabled":true}}}}', '', '{"type":"text"}']);
    }
}