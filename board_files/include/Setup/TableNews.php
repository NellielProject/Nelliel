<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableNews extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = NEWS_TABLE;
        $this->columns = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => true],
            'poster_id' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'time' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => false],
            'headline' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'text' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false]];
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
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            poster_id       VARCHAR(255) NOT NULL,
            time            BIGINT NOT NULL,
            headline        VARCHAR(255) NOT NULL,
            text            TEXT NOT NULL
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        ;
    }
}