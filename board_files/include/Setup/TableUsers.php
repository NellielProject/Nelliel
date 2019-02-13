<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableUsers extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = USERS_TABLE;
        $this->columns = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => true],
            'user_id' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'display_name' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'user_password' => ['pdo_type' => PDO::PARAM_STR, 'auto_inc' => false],
            'active' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => false],
            'super_admin' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => false],
            'last_login' => ['pdo_type' => PDO::PARAM_INT, 'auto_inc' => false]];
        $this->splitColumnInfo();
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
            entry               " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            user_id             VARCHAR(255) NOT NULL,
            display_name        VARCHAR(255) DEFAULT NULL,
            user_password       VARCHAR(255) DEFAULT NULL,
            active              SMALLINT NOT NULL DEFAULT 0,
            super_admin         SMALLINT NOT NULL DEFAULT 0,
            last_login          BIGINT DEFAULT NULL
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        if (DEFAULTADMIN === '' || DEFAULTADMIN_PASS === '')
        {
            return;
        }

        $prepared = $this->database->prepare('SELECT 1 FROM "' . $this->table_name . '" WHERE "user_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [DEFAULTADMIN], PDO::FETCH_COLUMN);

        if($result !== false)
        {
            return;
        }

        $this->insertDefaultRow([DEFAULTADMIN, 'Super Admin', nel_password_hash(DEFAULTADMIN_PASS, NEL_PASSWORD_ALGORITHM), 1, 1, null]);
    }
}