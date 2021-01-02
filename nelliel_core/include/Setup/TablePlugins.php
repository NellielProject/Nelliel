<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TablePlugins extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_PLUGINS_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'plugin_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'enabled' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'info' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry       " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            plugin_id   VARCHAR(100) NOT NULL UNIQUE,
            enabled     SMALLINT NOT NULL DEFAULT 0,
            info        TEXT NOT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
    }
}