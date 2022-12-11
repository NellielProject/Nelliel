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
            'plugin_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'directory' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'initializer' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'parsed_ini' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'enabled' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT]];
        $this->column_checks = [
            'plugin_id' => ['row_check' => true, 'auto_inc' => false],
            'directory' => ['row_check' => false, 'auto_inc' => false],
            'initializer' => ['row_check' => false, 'auto_inc' => false],
            'parsed_ini' => ['row_check' => false, 'auto_inc' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            plugin_id   VARCHAR(50) NOT NULL,
            directory   VARCHAR(255) NOT NULL,
            initializer VARCHAR(255) NOT NULL,
            parsed_ini  TEXT NOT NULL,
            enabled     SMALLINT NOT NULL DEFAULT 0,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (plugin_id)
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
    }
}