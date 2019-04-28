<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableSiteConfig extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = SITE_CONFIG_TABLE;
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

    public function setup()
    {
        $this->createTable();
        $this->insertDefaults();
    }

    public function createTable(array $other_tables = null)
    {
        $auto_inc = $this->sql_helpers->autoincrementColumn('INTEGER');
        $options = $this->sql_helpers->tableOptions();
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

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'home_page', '/', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'crypt', 'string', 'post_password_algorithm', 'sha256', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'crypt', 'string', 'secure_tripcode_algorithm', 'sha256', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'crypt', 'boolean', 'do_password_rehash', '0', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'output', 'string', 'index_filename_format', 'index-%d', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'output', 'string', 'thread_filename_format', 'thread-%d', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'template_id', 'nelliel-template-basic', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'locale', 'en_US', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'recaptcha_site_key', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'recaptcha_sekrit_key', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'name', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'slogan', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'favicon', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'string', 'banner', '', 0]);
        $this->insertDefaultRow(['core_setting', 'nelliel', 'general', 'boolean', 'display_render_timer', '1', 0]);
    }
}