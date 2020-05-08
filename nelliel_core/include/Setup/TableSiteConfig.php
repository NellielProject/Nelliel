<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableSiteConfig extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_SITE_CONFIG_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'config_type' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'config_owner' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'config_category' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'data_type' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'config_name' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'setting' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'select_type' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry               " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            config_type         VARCHAR(255) DEFAULT NULL,
            config_owner        VARCHAR(255) NOT NULL,
            config_category     VARCHAR(255) DEFAULT NULL,
            data_type           VARCHAR(255) DEFAULT NULL,
            config_name         VARCHAR(255) NOT NULL,
            setting             VARCHAR(255) NOT NULL,
            select_type         SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'home_page', '/', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'crypt', 'string', 'post_password_algorithm', 'sha256', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'crypt', 'string', 'secure_tripcode_algorithm', 'sha256', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'crypt', 'boolean', 'do_password_rehash', '0', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'output', 'string', 'index_filename_format', 'index-%d', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'output', 'string', 'thread_filename_format', 'thread-%d', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'template_id', 'template-nelliel-basic', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'locale', 'en_US', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'name', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'slogan', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'favicon', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'banner', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'recaptcha_site_key', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'recaptcha_sekrit_key', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'recaptcha_type', 'CHECKBOX', 1]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'integer', 'captcha_timeout', '1800', 0, 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'integer', 'captcha_rate_limit', '12', 0, 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'integer', 'captcha_width', '250', 0, 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'integer', 'captcha_height', '80', 0, 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'integer', 'captcha_character_count', '5', 0, 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'boolean', 'use_login_captcha', '0', 0, 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'boolean', 'use_login_recaptcha', '0', 0, 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'boolean', 'use_register_captcha', '0', 0, 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'boolean', 'use_register_recaptcha', '0', 0, 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'graphics_handler', 'GD', 1, 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'boolean', 'overboard_active', '0', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'overboard_uri', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'integer', 'overboard_update_interval', 30, 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'boolean', 'overboard_sfw_active', '0', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'overboard_sfw_uri', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'integer', 'overboard_sfw_update_interval', 30, 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'boolean', 'user_board_creation', '0', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'integer', 'max_boards_per_user', '1', 0, 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'boolean', 'display_render_timer', '1', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'integer', 'login_delay', '3', 0, 0]);

    }
}