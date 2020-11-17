<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableBoardData extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_BOARD_DATA_TABLE;
        $this->columns_data = [
            'board_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'db_prefix' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'locked' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            board_id        VARCHAR(50) PRIMARY KEY NOT NULL,
            board_uri       VARCHAR(50) NOT NULL,
            hashed_board_id " . $this->sql_compatibility->sqlAlternatives('BINARY', '32') . " NOT NULL,
            db_prefix       VARCHAR(20) NOT NULL UNIQUE,
            locked          SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
        ;
    }
}