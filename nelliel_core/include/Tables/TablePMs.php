<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TablePMs extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_PMS_TABLE;
        $this->columns_data = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'sender' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'recipient' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'message' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'time_sent' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'message_read' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->columns_data = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'sender' => ['row_check' => false, 'auto_inc' => false],
            'recipient' => ['row_check' => false, 'auto_inc' => false],
            'message' => ['row_check' => false, 'auto_inc' => false],
            'time_sent' => ['row_check' => false, 'auto_inc' => false],
            'message_read' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            sender          VARCHAR(50) NOT NULL,
            recipient       VARCHAR(50) NOT NULL,
            message         TEXT NOT NULL,
            time_sent       BIGINT NOT NULL,
            message_read    SMALLINT NOT NULL DEFAULT 0,
            moar            TEXT DEFAULT NULL
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