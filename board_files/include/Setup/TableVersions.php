<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableVersions extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = VERSIONS_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'item_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'item_type' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'structure_version' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'data_version' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false]];
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
            item_id                 VARCHAR(255) NOT NULL,
            item_type               VARCHAR(255) NOT NULL,
            structure_version       SMALLINT NOT NULL DEFAULT 0,
            data_version            SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow([ASSETS_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([BANS_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([BOARD_DATA_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([BOARD_DEFAULTS_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([CAPTCHA_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([FILE_FILTERS_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([FILETYPES_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([LOGIN_ATTEMPTS_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([NEWS_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([PERMISSIONS_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([REPORTS_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([ROLE_PERMISSIONS_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([ROLES_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([SITE_CONFIG_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([TEMPLATES_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([USER_ROLES_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([USERS_TABLE, "table", '1', '1']);
        $this->insertDefaultRow([VERSIONS_TABLE, "table", '1', '1']);
    }
}