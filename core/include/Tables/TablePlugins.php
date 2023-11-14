<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TablePlugins extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'plugin_id' => 'string',
        'directory' => 'string',
        'initializer' => 'string',
        'parsed_ini' => 'string',
        'enabled' => 'boolean',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'plugin_id' => PDO::PARAM_STR,
        'directory' => PDO::PARAM_STR,
        'initializer' => PDO::PARAM_STR,
        'parsed_ini' => PDO::PARAM_STR,
        'enabled' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_PLUGINS_TABLE;
        $this->column_checks = [
            'plugin_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'directory' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'initializer' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'parsed_ini' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
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
            moar        ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
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