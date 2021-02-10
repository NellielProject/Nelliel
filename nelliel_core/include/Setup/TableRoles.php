<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableRoles extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_ROLES_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'role_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'role_level' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'role_title' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'capcode' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            role_id         VARCHAR(50) NOT NULL UNIQUE,
            role_level      SMALLINT NOT NULL DEFAULT 0,
            role_title      VARCHAR(255) NOT NULL,
            capcode         TEXT DEFAULT NULL,
            moar            TEXT DEFAULT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['SITE_ADMIN', 100, 'Site Administrator', 'Site Administrator']);
        $this->insertDefaultRow(['BOARD_OWNER', 75, 'Board Owner', 'Board Owner']);
        $this->insertDefaultRow(['MODERATOR', 50, 'Moderator', 'Moderator']);
        $this->insertDefaultRow(['JANITOR', 25, 'Janitor', 'Janitor']);
        $this->insertDefaultRow(['BASIC_USER', 0, 'Basic', '']);
    }
}