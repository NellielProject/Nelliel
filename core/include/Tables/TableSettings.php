<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableSettings extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'setting_category' => 'string',
        'setting_owner' => 'string',
        'data_type' => 'string',
        'setting_name' => 'string',
        'default_value' => 'string',
        'setting_description' => 'string',
        'input_attributes' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'setting_category' => PDO::PARAM_STR,
        'setting_owner' => PDO::PARAM_STR,
        'data_type' => PDO::PARAM_STR,
        'setting_name' => PDO::PARAM_STR,
        'default_value' => PDO::PARAM_STR,
        'setting_description' => PDO::PARAM_STR,
        'input_attributes' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_SETTINGS_TABLE;
        $this->column_checks = [
            'setting_category' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'setting_owner' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'data_type' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'setting_name' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'default_value' => ['row_check' => false, 'auto_inc' => false, 'update' => true],
            'setting_description' => ['row_check' => false, 'auto_inc' => false, 'update' => true],
            'input_attributes' => ['row_check' => false, 'auto_inc' => false, 'update' => true],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            setting_category    VARCHAR(50) NOT NULL,
            setting_owner       VARCHAR(50) NOT NULL,
            data_type           VARCHAR(50) NOT NULL,
            setting_name        VARCHAR(50) NOT NULL,
            default_value       ' . $this->sql_compatibility->textType('LONGTEXT') . ' NOT NULL,
            setting_description TEXT NOT NULL,
            input_attributes    TEXT NOT NULL,
            moar                ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (setting_category, setting_name)
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {

    }

    public function insertDefaults()
    {
        // Site
        // General
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'name', '', 'Site name.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'show_name', '1', 'Display site name in header.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'description', '', 'Site description.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'show_description', '1', 'Display site description in header.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'favicon', '', 'Site favicon.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'show_favicon', '0', 'Show site favicon.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'generate_home_page', '0', 'Generate a standard home page.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'locale', NEL_DEFAULT_LOCALE, 'Default locale for site. Use ISO language and country codes separated by underscore.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'time_zone', 'UTC', 'Default time zone used on the site.', '{"type":"select"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'only_alphanumeric_board_ids', '1', 'Allow only alphanumeric board IDs.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'default_source_subdirectory', 'source', 'Default name for the source subdirectory.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'default_preview_subdirectory', 'preview', 'Default name for the preview subdirectory.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'default_page_subdirectory', 'threads', 'Default name for the page subdirectory.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'default_archive_subdirectory', 'archive', 'Default name for the archive subdirectory.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'allow_custom_subdirectories', '0', 'Allow custom subdirectory names when creating a board.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'only_alphanumeric_subdirectories', '1', 'Allow only alphanumeric characters for custom subdirectory names.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'max_subdirectory_length', '50', 'Maximum characters allowed in a custom board subdirectory name.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'max_report_items', '5', 'Maximum items that can be reported at one time.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'max_delete_items', '5', 'Maximum items that can be deleted at one time.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'global_announcement', '', 'Global announcement shown on all boards.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'uri_display_format', '/%s/', 'Format to use when displaying board URIs.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'shell_path', '/usr/local/bin', 'Append this to the path when executing shell commands. Multiple directories must be separated by :', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_content_disclaimer', '', 'Site-wide disclaimer added to the bottom of posts.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_footer_text', '', 'Additional text to put in the footer site-wide.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'max_board_pages', '10', 'Maximum static pages that can be created for a specific board.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'allow_user_registration', '0', 'Allow users to register an account.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'pm_snippet_length', '75', 'Maximum length of private message snippets.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'min_time_between_site_stat_updates', '30', 'Minimum time between site statistics updates (seconds).', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'min_time_between_board_stat_updates', '30', 'Minimum time between board statistics updates (seconds).', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'max_recent_posts', '100', 'Maximum number of recent posts from all boards combined.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'absolute_url_protocol', 'https', 'Protocol used when generating absolute urls to the site.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_domain', 'localhost', 'Domain of the site. Used for generating absolute URLs.', '{"type":"text"}']);


        // Banners
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'show_top_banners', '0', 'Display site banners at the top of public site pages.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'show_bottom_banners', '0', 'Display site banners at the bottom of public site pages.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'show_top_banners_on_boards', '0', 'Display site banners at the top of public board pages.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'show_bottom_banners_on_boards', '0', 'Display site banners at the bottom of public board pages.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'banner_display_width', '300', 'Display width of site banners.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'banner_display_height', '100', 'Display height of site banners.', '{"type":"number"}']);

        // Filenames and Structure
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'index_filename_format', 'index%d', 'Basename for board index pages after the first. (sprintf)', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'first_index_filename_format', 'index', 'Basename for the first board index page. (sprintf)', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'thread_filename_format', '%d', 'Basename format for thread files. (sprintf)', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'slug_thread_filename_format', '%s', 'Basename format for thread files when using slugified URLs. (sprintf)', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'first_posts_filename_format', '-first%d', 'Format of string appended to basename for first posts.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'last_posts_filename_format', '-last%d', 'Format of string appended to basename for last posts.', '{"type":"text"}']);

        // Rendering
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'base_image_set', 'images-nelliel-basic', 'Base image set.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'default_style', 'style-nelliel', 'Default style for site pages and control panels.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'show_blotter', '1', 'Show the short list of blotter entries.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'small_blotter_limit', '3', 'Maximum entries to show in the blotter short list.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'template_id', 'template-nelliel-basic', 'ID of default template for site', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'show_render_timer', '1', 'Show the rendering timer.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_referrer_policy', 'strict-origin-when-cross-origin', 'Referrer policy for the site.', '{"type":"select"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'external_link_referrer_policy', 'strict-origin-when-cross-origin', 'Referrer policy for external links. Overrides site policy if different.', '{"type":"select"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'nofollow_external_links', '1', 'Add rel="nofollow" to external links.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'max_page_regen_time', '0', 'How long the script can take to regenerate board or site pages. 0 sets unlimited time.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'private_message_time_format', 'Y/m/d l H:i', 'Time format for private messages. (PHP DateTime format).', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'blotter_time_format', 'Y/m/d', 'Time format for blotter posts. (PHP DateTime format).', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'news_time_format', 'Y/m/d l H:i T', 'Time format for news posts. (PHP DateTime format).', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'control_panel_list_time_format', 'Y/m/d (D) H:i:s', 'Time format for entries in control panel lists. (PHP DateTime format).', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'show_top_styles', '1', 'Show styles menu in the header.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'show_bottom_styles', '0', 'Show styles menu in the footer.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'error_message_header', 'oh god how did this get here', 'Title shown for error messages.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'show_error_images', '1', 'Show images on error page.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'error_image_set', 'images-nelliel-basic', 'Error page image set.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'error_image_max_size', '350', 'Maximum dimensions for error page images.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'pagination_default_entries', '50', 'Default number of entries on a page.', '{"type":"number"}']);

        // Uploads
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'graphics_handler', 'GD', 'Preferred graphics handler', '{"type":"select"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'imagemagick_args', '%s -auto-orient -thumbnail %dx%d -quality %d %s', 'Arguments given to ImageMagick for creating still image previews.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'imagemagick_animated_args', '%s -auto-orient -coalesce -thumbnail %dx%d %s', 'Arguments given to ImageMagick for creating animated previews.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'graphicsmagick_args', '%s -auto-orient -thumbnail %dx%d -quality %d %s', 'Arguments given to GraphicsMagick for creating still image previews.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'graphicsmagick_animated_args', '%s -auto-orient -coalesce -thumbnail %dx%d %s', 'Arguments given to GraphicsMagick for creating animated previews.', '{"type":"text"}']);

        // Hashing and Security
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'secure_tripcode_algorithm', 'sha256', 'Secure tripcode hash algorithm.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'do_password_rehash', '0', 'Rehash account passwords.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'login_delay', '3', 'Delay between login attempts.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'session_length', '2592000', 'Session timeout (seconds), 0 to disable. Default is 1 month.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'store_unhashed_ip', '1', 'Store unhashed IP addresses; (hashed IP will always be stored).', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'use_dnsbl', '0', 'Use DNSBL to check incoming posts.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'dnsbl_exceptions', '[]', 'IPs that are exempt from DNSBL checks. Enter as JSON array of strings.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'visitor_id_lifespan', '31536000', 'How long a visitor ID will be valid (seconds).', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'ipv6_identification_cidr', '60', 'CIDR that will be used on an IPv6 address for identification purposes.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'ipv4_small_subnet_cidr', '24', 'CIDR for small IPv4 hashed subnet.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'ipv4_large_subnet_cidr', '16', 'CIDR for large IPv4 hashed subnet.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'ipv6_small_subnet_cidr', '48', 'CIDR for small IPv6 hashed subnet.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'ipv6_large_subnet_cidr', '32', 'CIDR for large IPv6 hashed subnet.', '{"type":"number"}']);

        // CAPTCHA
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'enable_captchas', '1', 'Enable CAPTCHAs. All enabled CAPTCHA implementations will be used.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'use_native_captcha', '1', 'Use Nelliel\'s native CAPTCHA.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'captcha_characters', 'bcdfghjkmnpqrstvwxyz23456789', 'Characters to use in the CAPTCHA. Stick to alphanumeric for best results.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'captcha_max_lines', '6', 'Maximum lines to generate. O disables lines.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'captcha_max_character_rotation', '40', 'Maximum angle characters can be rotated.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'captcha_max_arcs', '6', 'Maximum arcs to generate. O disables arcs.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'captcha_width', '250', 'Width of CAPTCHA image.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'captcha_height', '80', 'Height of CAPTCHA image.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'captcha_character_count', '5', 'Number of characters in CAPTCHA.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'captcha_timeout', '1800', 'CAPTCHA timeout (seconds).', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'captcha_rate_limit', '12', 'CAPTCHA requests per IP in one minute (0 to disable check).', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'use_login_captcha', '0', 'Use CAPTCHAs for login.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'use_register_captcha', '0', 'Use CAPTCHAs for registration.', '{"type":"checkbox"}']);

        // Overboard
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'overboard_active', '0', 'Enable overboard.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'overboard_uri', 'overboard', 'Overboard URI.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'overboard_name', 'Overboard', 'Name of the overboard.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'overboard_catalog', '0', 'Enable catalog view for the overboard.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'overboard_threads', '20', 'Maximum threads on the overboard.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'overboard_thread_replies', '5', 'How many replies to a thread should be displayed on the overboard.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'nsfl_on_overboard', '0', 'Include NSFL content on the overboard.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'sfw_overboard_active', '0', 'Enable SFW overboard.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'sfw_overboard_uri', 'sfwoverboard', 'SFW overboard URI.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'sfw_overboard_name', 'SFW Overboard', 'Name of the SFW overboard.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'sfw_overboard_catalog', '0', 'Enable catalog view for the SFW overboard.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'sfw_overboard_threads', '20', 'Maximum threads on the SFW overboard.', '{"type":"number"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'integer', 'sfw_overboard_thread_replies', '5', 'How many replies to a thread should be displayed on the SFW overboard.', '{"type":"number"}']);

        // Site Links
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_navigation_link_set', '["site_links_home", "site_links_news", "site_links_faq", "site_links_about_nelliel", "site_links_blank_page", "site_links_account"]', 'Set of links for general site navigation.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'logged_in_link_set', '["site_links_site_panel", "site_links_global_panel", "site_links_board_panel", "site_links_board_list", "site_links_logout"]', 'Set of links that appear while logged in.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'boolean', 'translate_site_links', '1', 'Translate site navigation text when possible.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_links_left_bracket', '[', 'Bracket on the left side of site menu links.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_links_right_bracket', ']', 'Bracket on the left side of site menu links.', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_links_home', 'Home', 'Home', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_links_news', 'News', 'News', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_links_faq', 'FAQ', 'FAQ', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_links_about_nelliel', 'About Nelliel', 'About Nelliel', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_links_blank_page', 'Blank Page', 'Blank Page', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_links_account', 'Account', 'Account', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_links_site_panel', 'Site Panel', 'Site Panel', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_links_global_panel', 'Global Panel', 'Global Panel', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_links_board_panel', 'Board Panel', 'Global Panel', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_links_board_list', 'Board List', 'Board List', '{"type":"text"}']);
        $this->insertDefaultRow(['site', 'nelliel', 'string', 'site_links_logout', 'Logout', 'Logout', '{"type":"text"}']);

        // Board
        // General
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'name', '', 'Board name', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_name', '1', 'Display board name in header.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'description', '', 'Board description.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_description', '1', 'Display board description in header.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'favicon', '', 'Board favicon.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_favicon', '0', 'Show board favicon.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'locale', NEL_DEFAULT_LOCALE, 'Locale for this board. Use ISO language and country codes separated by underscore.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'time_zone', 'UTC', 'Default time zone used for this board.', '{"type":"select"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'exclude_from_overboards', '0', 'Exclude threads on this board from the overboards.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'user_delete_own', '1', 'Let users delete own posts and content.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_op_thread_moderation', '0', 'Let OP delete posts and uploads within their thread.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'delete_post_renzoku', '0', 'Cooldown after posting before user can delete their post (seconds).', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'delete_post_time_limit', '0', 'Maximum time until user can no longer delete their post (seconds). 0 to disable.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'safety_level', 'SFW', 'General safety level of content on this board.', '{"type":"select"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'board_content_disclaimer', '', 'Disclaimer added to the bottom of posts on this board.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'board_footer_text', '', 'Additional text to put in the footer for this board.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'enabled_styles', '["style-nelliel","style-nelliel-2","style-nelliel-classic","style-burichan","style-futaba","style-nigra"]', 'Styles which users can choose from.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_moving_replies', '1', 'Let individual replies from threads be moved.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_moving_uploads', '1', 'Let files and embeds be moved between posts.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_shadow_message', '1', 'Give the option of leaving a shadow message when moving or merging threads.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'shadow_message_moved', 'This thread has been moved to %s', 'Shadow thread message for moved threads. %s will be filled in with a cite link to the thread\'s new location. (sprintf).', '{"type":"textarea"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'shadow_message_merged', 'This thread has been merged into %s', 'Shadow thread message for merged threads. %s will be filled in with a cite link to the thread\'s new location. (sprintf)', '{"type":"textarea"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_dice_rolls', '1', 'Allow posters to use dice rolls.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_dice', '99', 'Maximum number of dice that can be rolled at once.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_dice_sides', '999', 'Maximum sides on dice.', '{"type":"number"}']);

        // Banners
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_top_banners', '0', 'Display board banners at the top of public pages.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_bottom_banners', '0', 'Display board banners at the bottom of public pages.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'banner_display_width', '300', 'Display width of site banners.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'banner_display_height', '100', 'Display height of site banners.', '{"type":"number"}']);

        // Bans
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'must_see_ban', '1', 'Bans must be seen at least once before expiration purge.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'ban_page_extra_text', '', 'Extra text that can be displayed on the ban page.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_ban_appeals', '1', 'Allow ban appeals.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'min_time_before_ban_appeal', '21600', 'Minimum time before a ban can be appealed (seconds).', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_ban_appeals', '2', 'Maximum number of appeals per ban.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_ip_range_ban_appeals', '0', 'Allow appeals for IP range bans.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_ban_mod_name', '0', 'Display the username of who set the ban.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'ban_page_time_format', 'F jS, Y H:i e', 'Time format for the ban page. (PHP DateTime format).', '{"type":"text"}']);

        // R9K
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'r9k_enable_board', '0', 'Use R9K on this board.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'r9k_global_unoriginal_check', '0', 'Globally check for unoriginal content. Only covers content posted to boards in R9K mode.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'r9k_strip_repeating', '1', 'Remove repeating characters.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'r9k_include_unicode_letters', '0', 'Include Unicode letters when generating the hash. If disabled, only letters a-z are kept.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'r9k_unoriginal_mute', '1', 'Temporarily mute user when unoriginal content is detected.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'r9k_global_mute_check', '0', 'Count mutes from all boards.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'r9k_mute_time_range', '1209600', 'Time range to check for existing mutes (seconds).', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'r9k_mute_base_number', '2', 'Base number (n) when calculating mute time (n^x). The number of existing mutes is used for the exponent.', '{"type":"number"}']);

        // New Post Form
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_op_name_field', '1', 'Enable the name field for new threads.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_op_name', '0', 'Require something in the name field for new threads.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_reply_name_field', '1', 'Enable the name field for replies.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_reply_name', '0', 'Require something in the name field for replies.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'min_name_length', '0', 'Minimum length of name.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_name_length', '100', 'Maximum length of name.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_op_email_field', '1', 'Enable the email field for new threads.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_op_email', '0', 'Require something in the email field for new threads.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_reply_email_field', '1', 'Enable the email field for replies.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_reply_email', '0', 'Require something in the email field for replies.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'min_email_length', '0', 'Minimum length of email.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_email_length', '100', 'Maximum length of email.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_op_subject_field', '1', 'Enable the subject field for new threads.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_op_subject', '0', 'Require something in the subject field for new threads.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_reply_subject_field', '1', 'Enable the subject field for replies.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_reply_subject', '0', 'Require something in the subject field for replies.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'min_subject_length', '0', 'Minimum length of subject.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_subject_length', '100', 'Maximum length of subject.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_op_comment_field', '1', 'Enable the comment field for new threads.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_op_comment', '1', 'Require something in the comment field for new threads.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_reply_comment_field', '1', 'Enable the comment field for replies.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_reply_comment', '1', 'Require something in the comment field for replies.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'min_comment_length', '0', 'Minimum length of comment.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_comment_length', '5000', 'Maximum length of comment.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_fgsfds_field', '1', 'Display the FGSFDS field.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_fgsfds_menu', '0', 'Replace the FGSFDS field with a menu of preset options.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_password_field', '1', 'Enable the password field.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'truncate_long_fields', '0', 'Truncate fields that are too long instead of giving an error.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'forced_anonymous', '0', 'Force anonymous posting. This disables the name and email fields (overrides field settings).', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'list_file_formats', '1', 'List the enabled formats.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'list_file_extensions', '0', 'List the enabled extensions. If formats is selected as well the two will be combined.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_tripcode', '0', 'Require either a tripcode or secure tripcode when posting.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_no_markup', '1', 'Allow user to disable markup in their post. HTML escaping and other filters will still be applied.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'new_post_auto_subject', '0', 'New post form has subject field automatically filled by OP subject.', '{"type":"checkbox"}']);

        // Uploads
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_uploads', '1', 'Allow uploads (files, embeds, etc).', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_op_files', '1', 'Allow users to upload files when making a new thread.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_op_file', '1', 'Require a file when making a new thread.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_op_embeds', '0', 'Allow embedded content when making a new thread.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_op_embed', '0', 'Require embedded content when making a new thread.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_op_files', '1', 'Maximum number of files in first post of a thread.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_op_embeds', '1', 'Maximum number of embeds in first post of a thread.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_op_total_uploads', '1', 'Maximum number of all uploads combined in first post of a thread.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_reply_files', '1', 'Allow users to upload files when replying.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_reply_file', '0', 'Require a file when replying.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_reply_embeds', '0', 'Allow embedded content in replies.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'require_reply_embed', '0', 'Require embedded content when replying.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_reply_files', '1', 'Maximum number of files in a reply post.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_reply_embeds', '1', 'Maximum number of embeds in a reply post.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_reply_total_uploads', '1', 'Maximum number of all uploads combined in a reply post.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'embed_replaces_file', '1', 'If embed URLs are given along with files, the files will be discarded and the embeds will be counted as files.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'check_board_file_duplicates', '0', 'Check for duplicate files in all posts on the board.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'check_op_file_duplicates', '1', 'Check for duplicate files in other thread OPs when creating new thread.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'check_thread_file_duplicates', '1', 'Check for duplicate files in current thread when replying.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'check_board_embed_duplicates', '0', 'Check for duplicate embed URLs in all posts on the board.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'check_op_embed_duplicates', '1', 'Check for duplicate embed URLs in other thread OPs when creating new thread.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'check_thread_embed_duplicates', '1', 'Check for duplicate embed URLs in current thread when replying.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'only_active_file_duplicates', '1', 'Only check for duplicate files in active threads (excludes the old thread buffer).', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'only_active_embed_duplicates', '1', 'Only check for duplicate embeds in active threads (excludes the old thread buffer).', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'limit_thread_uploads', '1', 'Limit the number of uploads in a thread.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_thread_uploads', '1000', 'Maximum number of uploads in a thread.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_filesize', '5242880', 'Maximum size of each file uploaded. Overrides category and format limits if they are higher. (bytes)', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_filesize_all_files', '5242880', 'Maximum total size of all files uploaded in one post. (bytes)', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_image_width', '8000', 'Maximum width of images (if detectable).', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_image_height', '8000', 'Maximum height of images (if detectable).', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_spoilers', '1', 'Enable spoilers for uploads.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'strip_exif', '0', 'Remove EXIF data from an image. Requires ExifTool.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'keep_icc', '1', 'When removing EXIF data, keep ICC color profiles.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'generate_file_sha256', '1', 'Generate SHA256 hash for uploaded files.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'generate_file_sha512', '1', 'Generate SHA512 hash for uploaded files.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'preferred_filename', 'timestamp', 'Preferred filename for uploads. If original filename is chosen, the filename will still go through some basicfiltering to avoid problems. If the preferred option is not available the name will default to timestamp.', '{"type":"select"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'keep_deleted_upload_entry', '1', 'When deleting an upload, remove it and any previews but retain the database entry instead of removing it entirely.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'store_exif_data', '1', 'Store EXIF data from images (if present).', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'enabled_filetypes', '{"graphics": {"enabled": true, "max_size": 0, "formats": ["jpeg", "gif", "png", "bmp", "webp"]}, "video": {"enabled": true, "max_size": 0, "formats": ["mpeg4", "webm"]}}', 'Which filetypes are enabled for uploading.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'file_deduplication', '0', 'If an uploaded file already exists, it will be referenced instead of processing the duplicate.', '{"type":"checkbox"}']);

        // Other New Post
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_sage', '1', 'Allow new posts to be saged.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_tripcodes', '1', 'Allow use of tripcodes.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'process_new_post_commands', '1', 'Process user commands (noko, sage, etc) when making a new post.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'allow_email_commands', '1', 'Allow commands in the email field.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'keep_email_commands', '0', 'Keep the email field input when it has commands.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'thread_renzoku', '120', 'Cooldown for new threads (seconds.)', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'reply_renzoku', '30', 'Cooldown for new replies (seconds).', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'upload_renzoku', '30', 'Cooldown for posts with uploads (seconds).', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'threads_per_hour_limit', '0', 'Maximum new threads per hour. 0 to disable.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_cites', '30', 'Maximum cite in a comment.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_crossboard_cites', '15', 'Maximum cross-board cites in a comment.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_comment_urls', '15', 'Maximum URL links allowed in a comment.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'trim_comment_newlines_start', '0', 'Trim extra newlines and whitespace at start of comment.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'trim_comment_newlines_end', '1', 'Trim extra newlines and whitespace at the end of comment.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_anonymous_names', '1', 'Use the list of anonymous names when name field is empty or disabled.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'anonymous_names', '["Anonymous"]', 'Names that can be randomly chosen when a name is not provided or forced anonymous is on. Enter as JSON array of strings.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'always_noko', '0', 'Always return to thread (noko) after making a post.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'automatic_gets', '[]', 'List of post numbers to be automatically stickied as a GET. Enter as JSON array of integers.', '{"type":"textarea"}']);

        // Preview Generation
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'create_static_preview', '1', 'Create a static image preview whenever possible.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'static_preview_images_only', '0', 'Only create static previews for images.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'static_preview_format', 'jpg', 'Format used for static previews.', '{"type":"select"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'create_animated_preview', '0', 'Create an animated image preview when appropriate. Requires extra libraries.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'animated_preview_images_only', '1', 'Only create animated previews for images. Strongly recommended.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'animated_preview_format', 'gif', 'Format used for animated previews.', '{"type":"select"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'animated_preview_max_frames', '1000', 'Maximum number of frames to use in animated previews.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'jpeg_quality', '85', 'JPEG quality (1-100).', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'webp_quality', '75', 'WebP quality (1-100).', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_preview_width', '250', 'Maximum width when generating previews.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_preview_height', '250', 'Maximum height when generating previews.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_copy_for_small_preview', '0', 'For images smaller than preview dimensions, just use a copy of the original.', '{"type":"checkbox"}']);

        // Threads
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'limit_post_count', '1', 'Limit the number of posts in a thread.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_posts', '1000', 'Maximum posts per thread.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'limit_bump_count', '1', 'Limit the number of bumps a thread can have.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_bumps', '1000', 'Maximum bumps per thread.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'active_threads', '100', 'Active threads (shown in index) before buffer.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'thread_buffer', '100', 'Maximum old threads kept in buffer before archive/prune.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'old_threads', 'PRUNE', 'How to handle old threads.', '{"type":"select"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'auto_archive_min_replies', '0', 'Minimum number of replies for a thread to be automatically archived.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_archive_threads', '500', 'Maximum threads kept in archive (excluding permanent threads).', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'do_archive_pruning', '1', 'Prune oldest threads in archive when limit is reached.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_slug_length', '80', 'Maximum characters in the thread URL slug.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'slugify_thread_url', '0', 'Use semantic URL (slug) for thread.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'thread_no_delete_replies', '0', 'Minimum number of replies to prevent OP from deleting the thread. 0 to disable.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'thread_no_delete_time', '0', 'Number of seconds after creating thread that OP cannot delete the thread. 0 to disable.', '{"type":"number"}']);

        // Index Rendering
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_index', '1', 'Render the index pages.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_index_link', '1', 'Show link for the index in page navigation. Will not display if index is disabled.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'index_nav_top', '0', 'Display navigation at top of index page.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'index_nav_bottom', '1', 'Display navigation at bottom of index page.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'index_thread_replies', '5', 'How many replies to a thread should be displayed on the index page.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'index_sticky_replies', '1', 'How many replies to a stickied thread should be displayed on the index page.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'threads_per_page', '10', 'Threads per page.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_index_comment_lines', '15', 'How many lines of comment to display when abbreviated.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'first_posts_increments', '[100]', 'Increments for first X posts. Leave empty to disable. Enter as JSON array of integers.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'first_posts_threshold', '200', 'Minimum posts in a thread before generating first X posts.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'last_posts_increments', '[50,100,200,500]', 'Increments for last X posts. Leave empty to disable. Enter as JSON array of integers.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'last_posts_threshold', '100', 'Minimum posts in a thread before generating last X posts.', '{"type":"number"}']);

        // Catalog Rendering
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_catalog', '1', 'Render the catalog pages.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_catalog_link', '1', 'Show link for the catalog in page navigation. Will not display if catalog is disabled.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'catalog_nav_top', '1', 'Display navigation at top of catalog page.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'catalog_nav_bottom', '1', 'Display navigation at bottom of catalog page.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'catalog_tile_width', '250', 'Width of catalog tiles.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'catalog_tile_height', '300', 'Height of catalog tiles.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'catalog_show_multiple_uploads', '1', 'Show multiple upload previews.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'catalog_first_preview_full_size', '1', 'First preview in a multiple upload tile will retain the size of a single upload preview.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'catalog_first_preview_own_row', '1', 'First preview in a multiple upload tile will remain by itself on the top row.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'catalog_max_preview_display_width', '120', 'Maximum display width for a single catalog preview.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'catalog_max_preview_display_height', '120', 'Maximum display height for a single catalog preview.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'catalog_max_multi_preview_display_width', '60', 'Maximum display width for multiple catalog previews.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'catalog_max_multi_preview_display_height', '60', 'Maximum display height for multiple catalog previews.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'catalog_max_uploads_row', '3', 'Maximum number of uploads to display in each row for catalog entries.', '{"type":"number"}']);

        // Thread Rendering
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'subject_in_title', '1', 'Use the thread subject in the page title.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'slug_in_title', '1', 'Use the generated thread slug in the page title (replaces ubject).', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'thread_number_in_title', '1', 'If subject and slug are both empty or not being used, use the thread number in the page title.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'prefix_board_title', '1', 'Prefix the board title (URI and name) to the page title of threads.', '{"type":"checkbox"}']);

        // Post Rendering
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'post_time_format', 'Y/m/d (D) H:i:s', 'Time format for posts. (PHP DateTime format).', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'indent_marker', '>>', 'Indent marker next to replies.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'tripcode_marker', '!', 'Tripcode marker.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'no_comment_text', '(no comment)', 'Text when there is no comment.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'filter_zalgo', '1', 'Filter out Zalgo text. Note: There are rare cases where this may break text.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_poster_id', '0', 'Show ID for posters in a thread.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'poster_id_colors', '0', 'Use a color background for poster IDs.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'poster_id_length', '6', 'Characters for poster ID (limited by hash length).', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'create_url_links', '1', 'Convert URLs into links.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'url_protocols', 'http|https|ftp|sftp|irc|nntp', 'Protocols which will be parsed to links (must be separated with |)', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'url_prefix', '', 'Prefix that will be added to URLs.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'post_backlinks_header', '1', 'Display reply backlinks in post header.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'post_backlinks_footer', '0', 'Display reply backlinks in post footer.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'post_backlinks_label', 'Replies: ', 'Label for reply backlinks.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_poster_name', '1', 'Show poster name.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_tripcodes', '1', 'Show poster tripcodes.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_capcode', '1', 'Show poster capcode.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_post_subject', '1', 'Show the post subject.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_user_comments', '1', 'Show the user comments.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_mod_comments', '1', 'Show the mod comments.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'list_all_dice_rolls', '0', 'List the results of each dice roll.', '{"type":"checkbox"}']);

        // Upload Rendering
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_static_preview', '1', 'Display static image previews when available.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_animated_preview', '0', 'Display animated image previews when available.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_original_as_preview', '0', 'Use the original image instead of the generated previews.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_video_preview', '1', 'Display an image preview for videos if available. Replaces video embedding.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'embed_video_files', '1', 'Embed uploaded video files. Currently supports MP4 and WebM.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_file_image', '1', 'Use filetype image when a preview is not available.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_uploads_row', '3', 'Maximum number of uploads to display in each row.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_op_preview_display_width', '250', 'Maximum display width for OP file previews.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_op_preview_display_height', '250', 'Maximum display height for OP file previews.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_reply_preview_display_width', '250', 'Maximum display width for reply file previews.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_reply_preview_display_height', '250', 'Maximum display height for reply file previews.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_op_embed_display_width', '300', 'Maximum display width for embedded OP content.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_op_embed_display_height', '300', 'Maximum display height for embedded OP content.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_reply_embed_display_width', '300', 'Maximum display width for embedded reply content.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_reply_embed_display_height', '300', 'Maximum display height for embedded reply content.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_op_multi_display_width', '200', 'Maximum display width for OP multiple uploads.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_op_multi_display_height', '200', 'Maximum display height for OP multiple uploads.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_reply_multi_display_width', '200', 'Maximum display width for multiple reply uploads.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'max_reply_multi_display_height', '200', 'Maximum display height for multiple replyuploads.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'image_spoiler_cover', 'media/core/covers/spoiler.png', 'Cover image for spoilers.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'spoiler_display_name', 'spoiler.jpg', 'Displayed file name when spoiler cover is used. Leave blank to use normal display name.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'image_deleted_file', 'media/core/placeholders/deleted_file.png', 'Placeholder image for deleted file.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'image_deleted_embed', 'media/core/placeholders/deleted_embed.png', 'Placeholder image for deleted embed.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'filename_display_length', '25', 'Maximum characters of filename to display', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'embed_url_display_length', '25', 'Maximum characters of embed URL to display', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_original_name', '1', 'Show the original file name in the file link.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'display_deleted_placeholder', '1', 'Display placeholder image for deleted uploads. If entry was fully removed from database nothing will be displayed.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'enabled_content_ops', '[]', 'Which content ops will be available.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_download_link', '1', 'Show an immediate download link along with normal file link.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'download_original_name', '1', 'Download link will use original file name if available.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_display_ratio', '0', 'Show the display ratio for media that has dimensions.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'scale_upload_filesize_units', '0', 'Automatically choose unit prefixes based on upload filesize.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'preview_lazy_loading', '0', 'Use lazy loading for preview images.', '{"type":"checkbox"}']);

        // New Post Form Rendering
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_allowed_filetypes', '1', 'Show a list of allowed filetypes on the new post form.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_file_category_max_sizes', '1', 'Show the category maximum filesizes (if lower than general max).', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_allowed_embeds', '1', 'Show a list of allowed embeds on the new post form.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_form_max_filesize', '1', 'Show the maximum allowed filesize.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_thumbnailed_message', '1', 'Show message about large images being thumbnailed.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'scale_new_post_filesize_units', '1', 'Automatically choose unit prefix for max filesize.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'name_field_label', 'Name', 'Name field label.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'name_field_placeholder', '', 'Name field placeholder text.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'email_field_label', 'E-mail', 'Email field label.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'email_field_placeholder', '', 'Email field placeholder text.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'subject_field_label', 'Subject', 'Subject field label.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'subject_field_placeholder', '', 'Subject field placeholder text.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'comment_field_label', 'Comment', 'Comment field label.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'comment_field_placeholder', '', 'Comment field placeholder text.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'fgsfds_field_label', 'FGSFDS', 'FGSFDS field label.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'fgsfds_field_placeholder', 'Enter commands here', 'FGSFDS field placeholder text.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'password_field_label', 'Password', 'Password field label.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'password_field_placeholder', '', 'Password field placeholder text.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'files_form_label', 'Files', 'Files form label.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'embeds_form_label', 'Embed URLs', 'Embeds form label.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'flags_form_label', 'Flags', 'Flags form label.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'captcha_form_label', 'CAPTCHA', 'CAPTCHA form label.', '{"type":"text"}']);

        // Other Rendering
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'template_id', 'template-nelliel-basic', 'ID of template for board to use.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'default_style', 'style-nelliel', 'ID of default style for board.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'ui_image_set', 'images-nelliel-basic', 'Image set to use for UI elements.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'filetype_image_set', 'images-nelliel-basic', 'Image set to use for filetypes.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'image_set_fallback', '1', 'If a selected image set doesn\'t have an entry for something, attempt to get it from the base image set.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_render_timer', '1', 'Show the rendering timer.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'generate_catalog', '1', 'Generate catalog for this board.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'display_iec_filesize_units', '0', 'Display IEC units (KiB, MiB, etc.) for formatted filesize.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'binary_filesize_conversion', '1', 'Use binary for converting formatted filesize between prefixes.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'filesize_precision', '2', 'Precision of formatted filesize.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'filesize_unit_prefix', 'KB', 'Default unit prefix for filesizes.', '{"type":"select"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_top_styles', '1', 'Show styles menu in the header.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'show_bottom_styles', '1', 'Show styles menu in the footer.', '{"type":"checkbox"}']);

        // Anti-spam
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_post_captcha', '0', 'Use CAPTCHA for new posts and threads', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'use_report_captcha', '0', 'Use CAPTCHA for making reports', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'enable_early404', '0', 'Enable early 404. Threads past the specified page with less than the specified number of posts will be pruned when a new thread is made.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'early404_replies_threshold', '5', 'Minimum replies needed to avoid being pruned by early 404.', '{"type":"number"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'integer', 'early404_page_threshold', '3', 'Last page of the index before early 404 pruning begins. Threads beyond this page will be checked.', '{"type":"number"}']);

        // Moderator Links
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'thread_mod_options_link_set', '["mod_links_lock", "mod_links_unlock", "mod_links_sticky", "mod_links_unsticky", "mod_links_permasage", "mod_links_unpermasage", "mod_links_cyclic", "mod_links_non_cyclic", "mod_links_move", "mod_links_merge"]', 'Set of links for thread moderation.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'post_mod_options_link_set', '["mod_links_ban", "mod_links_delete", "mod_links_ban_and_delete", "mod_links_delete_by_ip", "mod_links_global_delete_by_ip", "mod_links_edit", "mod_links_move"]', 'Set of links for post moderation.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'upload_mod_options_link_set', '["mod_links_delete", "mod_links_move", "mod_links_spoiler", "mod_links_unspoiler"]', 'Set of links for upload moderation.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'translate_mod_links', '1', 'Translate moderator link text when possible.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_left_bracket', '[', 'Bracket on the left side of moderation links.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_right_bracket', ']', 'Bracket on the right side of moderation links.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_ban', 'Ban', 'Ban', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_delete', 'Delete', 'Delete', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_delete_by_ip', 'Delete By IP', 'Delete By IP', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_global_delete_by_ip', 'Global Delete By IP', 'Global Delete By IP', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_ban_and_delete', 'Ban + Delete', 'Ban + Delete', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_lock', 'Lock', 'Lock', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_unlock', 'Unlock', 'Unlock', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_sticky', 'Sticky', 'Sticky', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_unsticky', 'Unsticky', 'Unsticky', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_permasage', 'Permasage', 'Permasage', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_unpermasage', 'Unpermasage', 'Unpermasage', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_cyclic', 'Cyclic', 'Cyclic', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_non_cyclic', 'Non-cyclic', 'Non-cyclic', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_edit', 'Edit', 'Edit', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_move', 'Move', 'Move', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_merge', 'Merge', 'Merge', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_spoiler', 'Spoiler', 'Spoiler', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'mod_links_unspoiler', 'Unspoiler', 'Unspoiler', '{"type":"text"}']);

        // Content Links
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'thread_options_link_set', '["content_links_reply", "content_links_hide_thread", "content_links_expand_thread"]', 'Set of links for thread options.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'post_options_link_set', '["content_links_hide_post", "content_links_cite_post"]', 'Set of links for post options.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'upload_options_link_set', '["content_links_hide_file", "content_links_hide_embed", "content_links_show_upload_meta"]', 'Set of links for upload options.', '{"type":"textarea"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'boolean', 'translate_content_links', '1', 'Translate content link text when possible.', '{"type":"checkbox"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_left_bracket', '[', 'Bracket on the left side of content links.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_right_bracket', ']', 'Bracket on the right side of content links.', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_reply', 'Reply', 'Reply', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_expand_thread', 'Expand', 'Expand', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_collapse_thread', 'Collapse', 'Collapse', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_show_thread', 'Show Thread', 'Show Thread', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_hide_thread', 'Hide Thread', 'Hide Thread', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_show_post', 'Show Post', 'Show Post', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_hide_post', 'Hide Post', 'Hide Post', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_show_file', 'Show File', 'Show File', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_hide_file', 'Hide File', 'Hide File', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_show_embed', 'Show Embed', 'Show Embed', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_hide_embed', 'Hide Embed', 'Hide Embed', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_show_upload_meta', 'Show Meta', 'Show Meta', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_hide_upload_meta', 'Hide Meta', 'Hide Meta', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_cite_post', 'Cite', 'Cite', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_first_posts', 'First %d posts', 'First %d posts', '{"type":"text"}']);
        $this->insertDefaultRow(['board', 'nelliel', 'string', 'content_links_last_posts', 'Last %d posts', 'Last %d posts', '{"type":"text"}']);

    }
}