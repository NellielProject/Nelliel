<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableRoles extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->table_name = ROLES_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'role_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'role_level' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'role_title' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'capcode_text' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function setup()
    {
        $this->createTable();
        $this->insertDefaults();
    }

    public function createTable(array $other_tables = null)
    {
        $auto_inc = $this->sql_helpers->autoincrementColumn('INTEGER');
        $options = $this->sql_helpers->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            role_id         VARCHAR(255) NOT NULL,
            role_level      SMALLINT NOT NULL DEFAULT 0,
            role_title      VARCHAR(255) DEFAULT NULL,
            capcode_text    TEXT DEFAULT NULL
        ) " . $options . ";";

        return $this->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['SUPER_ADMIN', 9001, 'Super Administrator', '## God Mode ##']);
        $this->insertDefaultRow(['SITE_ADMIN', 1000, 'Site Administrator', '## Site Administrator ##']);
        $this->insertDefaultRow(['BOARD_ADMIN', 100, 'Board Administrator', '## Board Administrator ##']);
        $this->insertDefaultRow(['MOD', 50, 'Moderator', '## Moderator ##']);
        $this->insertDefaultRow(['JANITOR', 10, 'Janitor', '## Janitor ##']);
    }
}