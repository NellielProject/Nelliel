<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableLoginAttempts extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->table_name = LOGIN_ATTEMPTS_TABLE;
        $this->columns_data = [
            'ip_address' => ['pdo_type' => PDO::PARAM_LOB, 'row_check' => false, 'auto_inc' => false],
            'last_attempt' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false]];
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
            ip_address      " . $this->sql_helpers->sqlAlternatives('VARBINARY', '16') . " NOT NULL UNIQUE,
            last_attempt    BIGINT NOT NULL
        ) " . $options . ";";

        return $this->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        ;
    }
}