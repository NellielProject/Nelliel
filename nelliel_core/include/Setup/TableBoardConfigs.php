<?php

declare(strict_types=1);


namespace Nelliel\Setup;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableBoardConfigs extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_BOARD_CONFIGS_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'board_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'setting_name' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'setting_value' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'edit_lock' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'board_id' => ['row_check' => true, 'auto_inc' => false],
            'setting_name' => ['row_check' => true, 'auto_inc' => false],
            'setting_value' => ['row_check' => false, 'auto_inc' => false],
            'edit_lock' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            board_id        VARCHAR(50) NOT NULL,
            setting_name    VARCHAR(50) NOT NULL,
            setting_value   TEXT NOT NULL,
            edit_lock       SMALLINT NOT NULL DEFAULT 0,
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