<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableRoles extends TableHandler
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
            'capcode' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            role_id         VARCHAR(255) NOT NULL,
            role_level      SMALLINT NOT NULL DEFAULT 0,
            role_title      VARCHAR(255) DEFAULT NULL,
            capcode    TEXT DEFAULT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['NEL_SITE_ADMIN', 80, 'Site Administrator', '## Site Administrator ##']);
        $this->insertDefaultRow(['NEL_BOARD_OWNER', 60, 'Board Owner', '## Board Owner ##']);
        $this->insertDefaultRow(['MODERATOR', 40, 'Moderator', '## Moderator ##']);
        $this->insertDefaultRow(['JANITOR', 20, 'Janitor', '']);
        $this->insertDefaultRow(['BASIC_USER', 0, 'Basic', '']);
    }
}