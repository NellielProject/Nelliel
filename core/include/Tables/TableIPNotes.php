<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableIPNotes extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_IP_NOTES_TABLE;
        $this->columns_data = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'user_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'ip_address' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'hashed_ip_address' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'time' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'notes' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->columns_data = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'user_id' => ['row_check' => false, 'auto_inc' => false],
            'ip_address' => ['row_check' => false, 'auto_inc' => false],
            'hashed_ip_address' => ['row_check' => false, 'auto_inc' => false],
            'time' => ['row_check' => false, 'auto_inc' => false],
            'notes' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry               " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            user_id             VARCHAR(50) NOT NULL,
            ip_address          " . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            hashed_ip_address   VARCHAR(128) NOT NULL,
            time                BIGINT NOT NULL,
            notes               TEXT NOT NULL,
            moar                TEXT DEFAULT NULL
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