<?php

declare(strict_types=1);


namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableCites extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_CITES_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'source_board' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'source_thread' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => true, 'auto_inc' => false],
            'source_post' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => true, 'auto_inc' => false],
            'target_board' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'target_thread' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => true, 'auto_inc' => false],
            'target_post' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => true, 'auto_inc' => false]];
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
            CONSTRAINT fk1_" . $this->table_name . "_" . $other_tables['board_data_table'] . "
            FOREIGN KEY (source_board) REFERENCES " . $other_tables['board_data_table'] . " (board_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk2_" . $this->table_name . "_" . $other_tables['board_data_table'] . "
            FOREIGN KEY (target_board) REFERENCES " . $other_tables['board_data_table'] . " (board_id)
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