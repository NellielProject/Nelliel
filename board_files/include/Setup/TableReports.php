<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableReports extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = REPORTS_TABLE;
        $this->columns = [
            'report_id' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => true],
            'board_id' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'content_id' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'reason' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'reporter_ip' => ['pdo_type' => PDO::PARAM_LOB, 'auto_inc' => false]];
        $this->splitColumnInfo();
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
            report_id       " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            board_id        VARCHAR(255) NOT NULL,
            content_id      VARCHAR(255) NOT NULL,
            reason          VARCHAR(255) NOT NULL DEFAULT '',
            reporter_ip     " . $this->sql_helpers->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        ;
    }
}