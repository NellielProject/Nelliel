<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TablePlugins extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_PLUGINS_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'plugin_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'info' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'enabled' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT]];
        $this->column_checks = [
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
            info        TEXT NOT NULL,
            enabled     SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
    }
}