<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableWordFilters extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_WORDFILTERS_TABLE;
        $this->column_types = [
            'filter_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'board_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'text_match' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'replacement' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'is_regex' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'enabled' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'filter_id' => ['row_check' => false, 'auto_inc' => true],
            'board_id' => ['row_check' => false, 'auto_inc' => false],
            'text_match' => ['row_check' => false, 'auto_inc' => false],
            'replacement' => ['row_check' => false, 'auto_inc' => false],
            'is_regex' => ['row_check' => false, 'auto_inc' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            filter_id   ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            board_id    VARCHAR(50) DEFAULT NULL,
            text_match  TEXT NOT NULL,
            replacement TEXT NOT NULL,
            is_regex    SMALLINT NOT NULL DEFAULT 0,
            enabled     SMALLINT NOT NULL DEFAULT 0,
            moar        TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (filter_id),
            CONSTRAINT fk_wordfilters__domain_registry
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