<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableOverboard extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_OVERBOARD_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'thread_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'last_bump_time' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'last_bump_time_milli' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'sticky' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'board_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'thread_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'last_bump_time' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'last_bump_time_milli' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'sticky' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'board_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry                   " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            thread_id               INTEGER NOT NULL,
            last_bump_time          BIGINT NOT NULL,
            last_bump_time_milli    SMALLINT NOT NULL,
            board_id                VARCHAR(50) NOT NULL,
            sticky                  SMALLINT NOT NULL DEFAULT 0,
            moar                    TEXT DEFAULT NULL,
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