<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableCites extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_CITES_TABLE;
        $this->columns_data = [
            'source_board' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'source_thread' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'source_post' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'target_board' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'target_thread' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'target_post' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            source_board    VARCHAR(50) NOT NULL,
            source_thread   INTEGER DEFAULT NULL,
            source_post     INTEGER DEFAULT NULL,
            target_board    VARCHAR(50) NOT NULL,
            target_thread   INTEGER DEFAULT NULL,
            target_post     INTEGER DEFAULT NULL,
            CONSTRAINT fk_source_board_" . $other_tables['board_data_table'] . "_board_id
            FOREIGN KEY (source_board) REFERENCES " . $other_tables['board_data_table'] . " (board_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk_target_board_" . $other_tables['board_data_table'] . "_board_id
            FOREIGN KEY (target_board) REFERENCES " . $other_tables['board_data_table'] . " (board_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
        ;
    }
}