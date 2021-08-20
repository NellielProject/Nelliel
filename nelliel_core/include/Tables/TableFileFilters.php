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
        $this->table_name = NEL_FILES_FILTERS_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'hash_type' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'file_hash' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'file_notes' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'board_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'hash_type' => ['row_check' => false, 'auto_inc' => false],
            'file_hash' => ['row_check' => true, 'auto_inc' => false],
            'file_notes' => ['row_check' => false, 'auto_inc' => false],
            'board_id' => ['row_check' => true, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry       " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            hash_type   VARCHAR(50) NOT NULL,
            file_hash   " . $this->sql_compatibility->sqlAlternatives('VARBINARY', '64') . " NOT NULL,
            file_notes  TEXT DEFAULT NULL,
            board_id    VARCHAR(50) NOT NULL,
            moar        TEXT DEFAULT NULL,
            CONSTRAINT fk1_" . $this->table_name . "_" . NEL_DOMAIN_REGISTRY_TABLE . "
            FOREIGN KEY (board_id) REFERENCES " . NEL_DOMAIN_REGISTRY_TABLE . " (domain_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
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