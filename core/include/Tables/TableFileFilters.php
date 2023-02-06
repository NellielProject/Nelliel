<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableFileFilters extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_FILE_FILTERS_TABLE;
        $this->column_types = [
            'filter_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'hash_type' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'file_hash' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'notes' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'board_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'enabled' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'filter_id' => ['row_check' => false, 'auto_inc' => true, 'update' => false],
            'hash_type' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'file_hash' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'notes' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'board_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            filter_id   ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            hash_type   VARCHAR(50) NOT NULL,
            file_hash   VARCHAR(128) NOT NULL,
            notes       TEXT DEFAULT NULL,
            board_id    VARCHAR(50) NOT NULL,
            enabled     SMALLINT NOT NULL DEFAULT 0,
            moar        TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (filter_id),
            CONSTRAINT fk_file_filters__domain_registry
            FOREIGN KEY (board_id) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
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