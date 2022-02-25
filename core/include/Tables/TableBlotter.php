<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableBlotter extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_BLOTTER_TABLE;
        $this->column_types = [
            'record_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'time' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'text' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'record_id' => ['row_check' => true, 'auto_inc' => true],
            'time' => ['row_check' => false, 'auto_inc' => false],
            'text' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            record_id   ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            time        BIGINT NOT NULL,
            text        TEXT NOT NULL,
            moar        TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (record_id)
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