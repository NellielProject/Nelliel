<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableCites extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = CITES_TABLE;
        $this->columns = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => true],
            'source_board' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'source_post' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => false],
            'target_board' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'target_post' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => false]];
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
            entry                  " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            source_board    VARCHAR(255) NOT NULL,
            source_post     INTEGER NOT NULL,
            target_board    VARCHAR(255) NOT NULL,
            target_post     INTEGER NOT NULL
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        ;
    }
}