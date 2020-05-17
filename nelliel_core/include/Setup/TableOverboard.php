<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableOverboard extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_OVERBOARD_TABLE;
        $this->columns_data = [
        'ob_key' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
        'thread_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
        'last_bump_time' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
        'last_bump_time_milli' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
        'board_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
        'safety_level' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            ob_key                  VARCHAR(64) NOT NULL PRIMARY KEY,
            thread_id               INTEGER NOT NULL,
            last_bump_time          BIGINT NOT NULL,
            last_bump_time_milli    SMALLINT NOT NULL,
            board_id                VARCHAR(50) NOT NULL,
            safety_level            VARCHAR(20) NOT NULL,
            CONSTRAINT fk_board_id_" . $other_tables['board_data_table'] . "_board_id
            FOREIGN KEY (board_id) REFERENCES " . $other_tables['board_data_table'] . " (board_id)
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