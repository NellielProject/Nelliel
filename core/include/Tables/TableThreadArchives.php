<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableThreadArchives extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = '_archives';
        $this->columns_data = [
            'thread_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'thread_data' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'time_archived' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'permanent' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->columns_data = [
            'thread_id' => ['row_check' => true, 'auto_inc' => false],
            'thread_data' => ['row_check' => false, 'auto_inc' => false],
            'time_added' => ['row_check' => false, 'auto_inc' => false],
            'permanent' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            thread_id       INTEGER PRIMARY KEY NOT NULL,
            thread_data     TEXT NOT NULL,
            time_archived   BIGINT NOT NULL,
            permanent       SMALLINT NOT NULL DEFAULT 0,
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