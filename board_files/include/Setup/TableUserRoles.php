<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableUserRoles extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = USER_ROLES_TABLE;
        $this->columns = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => true],
            'user_id' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'role_id' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'board' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false]];
        $this->splitColumnInfo();
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
            entry       " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            user_id     VARCHAR(255) NOT NULL,
            role_id     VARCHAR(255) NOT NULL,
            board       VARCHAR(255) NOT NULL
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        if (DEFAULTADMIN === '' || DEFAULTADMIN_PASS === '')
        {
            return;
        }

        $this->insertDefaultRow([DEFAULTADMIN, 'SUPER_ADMIN', '']);
    }
}