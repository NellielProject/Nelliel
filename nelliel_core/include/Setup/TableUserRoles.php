<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableUserRoles extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_USER_ROLES_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'user_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'role_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'domain_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry       " . $auto_inc[0] . " NOT NULL " . $auto_inc[1] . " PRIMARY KEY,
            user_id     VARCHAR(50) NOT NULL,
            role_id     VARCHAR(50) NOT NULL,
            domain_id   VARCHAR(50) NOT NULL,
            CONSTRAINT fk_user_id_" . $other_tables['users_table'] . "_user_id
            FOREIGN KEY (user_id) REFERENCES " . $other_tables['users_table'] . " (user_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk_role_id_" . $other_tables['roles_table'] . "_role_id
            FOREIGN KEY (role_id) REFERENCES " . $other_tables['roles_table'] . " (role_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
        ;
    }
}