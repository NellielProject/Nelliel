<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableCaptcha extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = CAPTCHA_TABLE;
        $this->columns_data = [
            'key' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'text' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'domain_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'time_created' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'ip_address' => ['pdo_type' => PDO::PARAM_LOB, 'row_check' => false, 'auto_inc' => false],
            'hashed_ip' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function setup()
    {
        $this->createTable();
    }

    public function createTable(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            key             VARCHAR(255) NOT NULL PRIMARY KEY,
            text            VARCHAR(255) NOT NULL,
            domain_id       VARCHAR(255) NOT NULL,
            time_created    INTEGER NOT NULL,
            ip_address      " . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            hashed_ip       VARCHAR(255) NOT NULL
        ) " . $options . ";";

        return $this->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        ;
    }
}