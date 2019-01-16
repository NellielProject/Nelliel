<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableBans extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = BANS_TABLE;
        $this->columns = [
            'ban_id' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => true],
            'board_id' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'all_boards' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => false],
            'type' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'creator' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'ip_address_start' => ['pdo_type' => PDO::PARAM_LOB, 'auto_inc' => false],
            'ip_address_end' => ['pdo_type' => PDO::PARAM_LOB, 'auto_inc' => false],
            'reason' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'length' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => false],
            'start_time' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => false],
            'appeal' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'appeal_response' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'appeal_status' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false]];
        $this->splitColumnInfo();
    }

    public function setup()
    {
        $this->createTable();
    }

    public function createTable(array $other_tables = null)
    {
        $auto_inc = $this->sql_helpers->autoincrementColumn('INTEGER');
        $options = $this->sql_helpers->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            ban_id                  " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            board_id                VARCHAR(255) DEFAULT NULL,
            all_boards              SMALLINT NOT NULL DEFAULT 0,
            type                    VARCHAR(255) NOT NULL,
            creator                 VARCHAR(255) NOT NULL,
            ip_address_start        " . $this->sql_helpers->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            ip_address_end          " . $this->sql_helpers->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            reason                  TEXT DEFAULT NULL,
            length                  BIGINT NOT NULL,
            start_time              BIGINT NOT NULL,
            appeal                  TEXT DEFAULT NULL,
            appeal_response         TEXT DEFAULT NULL,
            appeal_status           SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        ;
    }
}