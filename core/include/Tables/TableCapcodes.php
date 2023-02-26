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
            'capcode_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'capcode' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'output' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'enabled' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'capcode_id' => ['row_check' => false, 'auto_inc' => true, 'update' => false],
            'capcode' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'output' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            capcode_id  ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            capcode     VARCHAR(255) NOT NULL,
            output      TEXT NOT NULL,
            enabled     SMALLINT NOT NULL DEFAULT 0,
            moar        ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (capcode_id)
        ) ' . $options . ';';

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