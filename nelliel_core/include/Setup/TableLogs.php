<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableLogs extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_LOGS_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'level' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'event_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'originator' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'ip_address' => ['pdo_type' => PDO::PARAM_LOB, 'row_check' => false, 'auto_inc' => false],
            'hashed_ip_address' => ['pdo_type' => PDO::PARAM_LOB, 'row_check' => false, 'auto_inc' => false],
            'time' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'message' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry               " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            level               INTEGER NOT NULL,
            domain_id           VARCHAR(50) DEFAULT NULL,
            event_id            VARCHAR(50) NOT NULL,
            originator          VARCHAR(50) DEFAULT NULL,
            ip_address          " . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            hashed_ip_address   " . $this->sql_compatibility->sqlAlternatives('VARBINARY', '32') . " DEFAULT NULL,
            time                INTEGER NOT NULL,
            message             TEXT NOT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
        ;
    }
}