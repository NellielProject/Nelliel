<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableCites extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_CITES_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'source_board' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'source_thread' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'source_post' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'target_board' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'target_thread' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'target_post' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'source_board' => ['row_check' => true, 'auto_inc' => false],
            'source_thread' => ['row_check' => true, 'auto_inc' => false],
            'source_post' => ['row_check' => true, 'auto_inc' => false],
            'target_board' => ['row_check' => true, 'auto_inc' => false],
            'target_thread' => ['row_check' => true, 'auto_inc' => false],
            'target_post' => ['row_check' => true, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            source_board    VARCHAR(50) NOT NULL,
            source_thread   INTEGER DEFAULT NULL,
            source_post     INTEGER DEFAULT NULL,
            target_board    VARCHAR(50) NOT NULL,
            target_thread   INTEGER DEFAULT NULL,
            target_post     INTEGER DEFAULT NULL,
            CONSTRAINT fk1_" . $this->table_name . "_" . NEL_DOMAIN_REGISTRY_TABLE . "
            FOREIGN KEY (source_board) REFERENCES " . NEL_DOMAIN_REGISTRY_TABLE . " (domain_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk2_" . $this->table_name . "_" . NEL_DOMAIN_REGISTRY_TABLE . "
            FOREIGN KEY (target_board) REFERENCES " . NEL_DOMAIN_REGISTRY_TABLE . " (domain_id)
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