<?php

declare(strict_types=1);


namespace Nelliel\Tables;

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
            'id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'type' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'original' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'current' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT]];
        $this->column_checks = [
            'id' => ['row_check' => true, 'auto_inc' => false],
            'type' => ['row_check' => true, 'auto_inc' => false],
            'original' => ['row_check' => false, 'auto_inc' => false],
            'current' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            id          VARCHAR(100) NOT NULL,
            type        VARCHAR(50) NOT NULL,
            original    SMALLINT NOT NULL DEFAULT 0,
            current     SMALLINT NOT NULL DEFAULT 0,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (id, type)
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