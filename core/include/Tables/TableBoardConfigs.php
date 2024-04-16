<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableBoardConfigs extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'board_id' => 'string',
        'setting_name' => 'string',
        'setting_value' => 'string',
        'edit_lock' => 'boolean',
        'stored_raw' => 'boolean',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'board_id' => PDO::PARAM_STR,
        'setting_name' => PDO::PARAM_STR,
        'setting_value' => PDO::PARAM_STR,
        'edit_lock' => PDO::PARAM_INT,
        'stored_raw' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];


    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_BOARD_CONFIGS_TABLE;
        $this->column_checks = [
            'board_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'setting_name' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'setting_value' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'edit_lock' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'stored_raw' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            board_id        VARCHAR(50) NOT NULL,
            setting_name    VARCHAR(50) NOT NULL,
            setting_value   ' . $this->sql_compatibility->textType('LONGTEXT') . ' NOT NULL,
            edit_lock       SMALLINT NOT NULL DEFAULT 0,
            stored_raw      SMALLINT NOT NULL DEFAULT 0,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (board_id, setting_name),
            CONSTRAINT fk_board_configs__domain_registry
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