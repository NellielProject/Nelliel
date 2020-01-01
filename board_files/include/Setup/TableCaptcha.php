<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableCaptcha extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->table_name = CAPTCHA_TABLE;
        $this->columns_data = [
            'cookie_key' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'answer_text' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'case_sensitive' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'time_created' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function setup()
    {
        $this->createTable();
    }

    public function createTable(array $other_tables = null)
    {
        $auto_inc = $this->sql_helpers->autoincrementColumn('INTEGER');
        $options = $this->sql_helpers->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            cookie_key      VARCHAR(128) NOT NULL PRIMARY KEY,
            answer_text     VARCHAR(255) NOT NULL,
            case_sensitive  SMALLINT NOT NULL DEFAULT 0,
            time_created    BIGINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $this->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        ;
    }
}