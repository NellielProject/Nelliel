<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableUsers extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_USERS_TABLE;
        $this->columns_data = [
            'user_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'display_name' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'user_password' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'hashed_user_id' => ['pdo_type' => PDO::PARAM_LOB, 'row_check' => false, 'auto_inc' => false],
            'active' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'locked' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'owner' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'last_login' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            user_id         VARCHAR(50) PRIMARY KEY NOT NULL,
            display_name    VARCHAR(255) NOT NULL,
            user_password   VARCHAR(255) NOT NULL,
            hashed_user_id  " . $this->sql_compatibility->sqlAlternatives('BINARY', '32') . " NOT NULL,
            active          SMALLINT NOT NULL DEFAULT 0,
            locked          SMALLINT NOT NULL DEFAULT 0,
            owner           SMALLINT NOT NULL DEFAULT 0,
            last_login      BIGINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
    }
}