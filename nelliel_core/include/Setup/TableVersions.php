<?php

declare(strict_types=1);


namespace Nelliel\Setup;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableVersions extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_VERSIONS_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'type' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'original' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'current' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'id' => ['row_check' => true, 'auto_inc' => false],
            'type' => ['row_check' => true, 'auto_inc' => false],
            'original' => ['row_check' => false, 'auto_inc' => false],
            'current' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry       " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            id          VARCHAR(100) NOT NULL,
            type        VARCHAR(50) NOT NULL,
            original    SMALLINT NOT NULL DEFAULT 0,
            current     SMALLINT NOT NULL DEFAULT 0
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