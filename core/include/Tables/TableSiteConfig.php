<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableSiteConfig extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_SITE_CONFIG_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'setting_name' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'setting_value' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'setting_name' => ['row_check' => true, 'auto_inc' => false],
            'setting_value' => ['row_check' => false, 'auto_inc' => false]];
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
            setting_value   TEXT NOT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        // General
        $this->insertDefaultRow(['name', '']);
        $this->insertDefaultRow(['show_name', '1']);
        $this->insertDefaultRow(['description', '']);
        $this->insertDefaultRow(['show_description', '1']);
        $this->insertDefaultRow(['favicon', '']);
        $this->insertDefaultRow(['show_favicon', '0']);
        $this->insertDefaultRow(['home_page', '']);
        $this->insertDefaultRow(['generate_home_page', '0']);
        $this->insertDefaultRow(['locale', 'en_US']);
        $this->insertDefaultRow(['only_alphanumeric_board_ids', '1']);
        $this->insertDefaultRow(['allow_custom_directories', '0']);
        $this->insertDefaultRow(['only_alphanumeric_directories', '1']);
        $this->insertDefaultRow(['max_report_items', '5']);
        $this->insertDefaultRow(['max_delete_items', '5']);
        $this->insertDefaultRow(['global_announcement', '']);
        $this->insertDefaultRow(['uri_display_format', '/%s/']);
        $this->insertDefaultRow(['shell_path', '/usr/local/bin']);
        $this->insertDefaultRow(['site_content_disclaimer', '']);
        $this->insertDefaultRow(['site_footer_text', '', 0]);

        // Banners
        $this->insertDefaultRow(['show_site_banners', '0']);
        $this->insertDefaultRow(['show_board_banners', '0']);
        $this->insertDefaultRow(['banner_display_width', '300']);
        $this->insertDefaultRow(['banner_display_height', '100']);

        // Bans
        $this->insertDefaultRow(['must_see_ban', '1']);
        $this->insertDefaultRow(['allow_ban_appeals', '1']);
        $this->insertDefaultRow(['min_time_before_ban_appeal', '3600']);

        // Filenames and Structure
        $this->insertDefaultRow(['index_filename_format', 'index%d']);
        $this->insertDefaultRow(['first_index_filename_format', 'index']);
        $this->insertDefaultRow(['thread_filename_format', '%d']);
        $this->insertDefaultRow(['slug_thread_filename_format', '%s']);
        $this->insertDefaultRow(['first_posts_filename_format', '-first%d']);
        $this->insertDefaultRow(['last_posts_filename_format', '-last%d']);

        // Rendering
        $this->insertDefaultRow(['base_icon_set', 'icons-nelliel-basic']);
        $this->insertDefaultRow(['default_style', 'style-nelliel']);
        $this->insertDefaultRow(['show_blotter', '1']);
        $this->insertDefaultRow(['small_blotter_limit', '3']);
        $this->insertDefaultRow(['template_id', 'template-nelliel-basic']);
        $this->insertDefaultRow(['display_render_timer', '1']);
        $this->insertDefaultRow(['site_referrer_policy', 'strict-origin-when-cross-origin']);
        $this->insertDefaultRow(['nofollow_external_links', '1']);

        // Uploads
        $this->insertDefaultRow(['graphics_handler', 'GD']);
        $this->insertDefaultRow(['imagemagick_args', '%s -auto-orient -thumbnail %dx%d -quality %d %s']);
        $this->insertDefaultRow(['imagemagick_animated_args', '%s -auto-orient -coalesce -thumbnail %dx%d %s']);
        $this->insertDefaultRow(['graphicsmagick_args', '%s -auto-orient -thumbnail %dx%d -quality %d %s']);
        $this->insertDefaultRow(['graphicsmagick_animated_args', '%s -auto-orient -coalesce -thumbnail %dx%d %s']);

        // Hashing and security
        $this->insertDefaultRow(['post_password_algorithm', 'sha256']);
        $this->insertDefaultRow(['secure_tripcode_algorithm', 'sha256']);
        $this->insertDefaultRow(['do_password_rehash', '0']);
        $this->insertDefaultRow(['login_delay', '3']);
        $this->insertDefaultRow(['session_length', '10800']);
        $this->insertDefaultRow(['store_unhashed_ip', '1']);
        $this->insertDefaultRow(['use_dnsbl', '0']);
        $this->insertDefaultRow(['dnsbl_exceptions', '[]']);

        // CAPTCHA
        $this->insertDefaultRow(['captcha_width', '250']);
        $this->insertDefaultRow(['captcha_height', '80']);
        $this->insertDefaultRow(['captcha_character_count', '5']);
        $this->insertDefaultRow(['captcha_timeout', '1800']);
        $this->insertDefaultRow(['captcha_rate_limit', '12']);
        $this->insertDefaultRow(['recaptcha_site_key', '']);
        $this->insertDefaultRow(['recaptcha_sekrit_key', '']);
        $this->insertDefaultRow(['recaptcha_type', 'CHECKBOX']);
        $this->insertDefaultRow(['use_login_captcha', '0']);
        $this->insertDefaultRow(['use_login_recaptcha', '0']);
        $this->insertDefaultRow(['use_register_captcha', '0']);
        $this->insertDefaultRow(['use_register_recaptcha', '0']);

        // Overboard
        $this->insertDefaultRow(['overboard_active', '0']);
        $this->insertDefaultRow(['overboard_uri', 'overboard']);
        $this->insertDefaultRow(['overboard_threads', '20']);
        $this->insertDefaultRow(['overboard_thread_replies', '5']);
        $this->insertDefaultRow(['nsfl_on_overboard', '']);
        $this->insertDefaultRow(['sfw_overboard_active', '0']);
        $this->insertDefaultRow(['sfw_overboard_uri', 'sfwoverboard']);
        $this->insertDefaultRow(['sfw_overboard_threads', '20']);
        $this->insertDefaultRow(['sfw_overboard_thread_replies', '5']);
    }
}