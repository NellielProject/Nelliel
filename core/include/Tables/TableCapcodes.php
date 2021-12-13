<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableCapcodes extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_CAPCODES_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'capcode_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'capcode_output' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'enabled' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'capcode_id' => ['row_check' => true, 'auto_inc' => false],
            'capcode_output' => ['row_check' => false, 'auto_inc' => false],
            'enabled' => ['row_check' => true, 'auto_inc' => false],
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
            capcode_id          VARCHAR(255) NOT NULL UNIQUE,
            capcode_output      TEXT NOT NULL,
            enabled             SMALLINT NOT NULL DEFAULT 0,
            moar                TEXT DEFAULT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['Site Owner', '<span class="capcode" style="color: fuchsia;"> ## Site Owner</span>', 1]);
        $this->insertDefaultRow(['Site Admin', '<span class="capcode" style="color: blue;"> ## Site Admin</span>', 1]);
        $this->insertDefaultRow(['Board Owner', '<span class="capcode" style="color: green;"> ## Board Owner</span>', 1]);
        $this->insertDefaultRow(['Moderator', '<span class="capcode" style="color: red;"> ## Moderator</span>', 1]);
        $this->insertDefaultRow(['Janitor', '<span class="capcode" style="color: orange;"> ## Janitor</span>', 1]);
        $this->insertDefaultRow(['', '<span class="capcode"> ## %s</span>', 1]);
    }
}