<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableTemplates extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = TEMPLATES_TABLE;
        $this->columns = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => true],
            'id' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'is_default' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'info' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false]];
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
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            id              VARCHAR(255) NOT NULL,
            is_default      SMALLINT NOT NULL DEFAULT 0,
            info            TEXT NOT NULL
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['template-nelliel-basic', 1, '{"id": "template-nelliel-basic","directory": "nelliel_basic","name": "Nelliel Basic Template","version": "1.0","description": "The basic template for Nelliel.","output_type": "html"}']);
    }
}