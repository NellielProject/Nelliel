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
        $this->sql_helpers = $sql_helpers;
        $this->table_name = CAPTCHA_TABLE;
        $this->columns = [
            'cookie_key' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'answer_text' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'case_sensitive' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => false],
            'time_created' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => false]];
        $this->splitColumnInfo();
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
            cookie_key              VARCHAR(255) NOT NULL PRIMARY KEY,
            answer_text             VARCHAR(255) NOT NULL,
            case_sensitive          SMALLINT NOT NULL DEFAULT 0,
            time_created            BIGINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        ;
    }
}