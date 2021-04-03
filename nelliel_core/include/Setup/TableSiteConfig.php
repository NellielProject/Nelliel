<?php

declare(strict_types=1);


namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableSiteConfig extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_SITE_CONFIG_TABLE;
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
        $this->insertDefaultRow(['home_page', '/', 0]);
        $this->insertDefaultRow(['locale', 'en_US', 0]);
        $this->insertDefaultRow(['only_alphanumeric_board_ids', '1', 0]);
        $this->insertDefaultRow(['max_report_items', '5', 0]);
        $this->insertDefaultRow(['max_delete_items', '5', 0]);
        $this->insertDefaultRow(['global_announcement', '', 0]);

        // Banners
        $this->insertDefaultRow(['show_site_banners', '1', 0]);
        $this->insertDefaultRow(['show_board_banners', '1', 0]);
        $this->insertDefaultRow(['banner_display_width', '300', 0]);
        $this->insertDefaultRow(['banner_display_height', '100', 0]);

        // Bans
        $this->insertDefaultRow(['must_see_ban', '1', 0]);
        $this->insertDefaultRow(['allow_ban_appeals', '1', 0]);
        $this->insertDefaultRow(['min_time_before_ban_appeal', '3600', 0]);

        // Posts and rendering
        $this->insertDefaultRow(['index_filename_format', 'index%d', 0]);
        $this->insertDefaultRow(['thread_filename_format', '%d', 0]);
        $this->insertDefaultRow(['template_id', 'template-nelliel-basic', 0]);
        $this->insertDefaultRow(['graphics_handler', 'GD', 0]);
        $this->insertDefaultRow(['noreferrer_nofollow', '0', 0]);
        $this->insertDefaultRow(['display_render_timer', '1', 0]);
        $this->insertDefaultRow(['site_content_disclaimer', '', 0]);

        // Hashing and security
        $this->insertDefaultRow(['post_password_algorithm', 'sha256', 0]);
        $this->insertDefaultRow(['secure_tripcode_algorithm', 'sha256', 0]);
        $this->insertDefaultRow(['do_password_rehash', '0', 0]);
        $this->insertDefaultRow(['login_delay', '3', 0]);
        $this->insertDefaultRow(['session_length', '10800', 0]);
        $this->insertDefaultRow(['store_unhashed_ip', '1', 0]);

        // CAPTCHA
        $this->insertDefaultRow(['captcha_width', '250', 0]);
        $this->insertDefaultRow(['captcha_height', '80', 0]);
        $this->insertDefaultRow(['captcha_character_count', '5', 0]);
        $this->insertDefaultRow(['captcha_timeout', '1800', 0]);
        $this->insertDefaultRow(['captcha_rate_limit', '12', 0]);
        $this->insertDefaultRow(['recaptcha_site_key', '', 0]);
        $this->insertDefaultRow(['recaptcha_sekrit_key', '', 0]);
        $this->insertDefaultRow(['recaptcha_type', 'CHECKBOX', 0]);
        $this->insertDefaultRow(['use_login_captcha', '0', 0]);
        $this->insertDefaultRow(['use_login_recaptcha', '0', 0]);
        $this->insertDefaultRow(['use_register_captcha', '0', 0]);
        $this->insertDefaultRow(['use_register_recaptcha', '0', 0]);

        // Overboard
        $this->insertDefaultRow(['overboard_active', '0', 0]);
        $this->insertDefaultRow(['overboard_uri', 'overboard', 0]);
        $this->insertDefaultRow(['overboard_threads', '20', 0]);
        $this->insertDefaultRow(['sfw_overboard_active', '0', 0]);
        $this->insertDefaultRow(['sfw_overboard_uri', 'sfwoverboard', 0]);
        $this->insertDefaultRow(['sfw_overboard_threads', '20', 0]);
        $this->insertDefaultRow(['nsfl_on_overboard', '', 0]);

        // Filetypes
        $this->insertDefaultRow(['enabled_filetypes', '{"graphics":{"enabled":true,"formats":{"jpeg":{"enabled":true},"gif":{"enabled":true},"png":{"enabled":true},"webp":{"enabled":true}}}}', 0, 0]);
    }
}