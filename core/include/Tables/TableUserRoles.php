<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableUserRoles extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_USER_ROLES_TABLE;
        $this->column_types = [
            'user_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'role_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'domain_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'user_id' => ['row_check' => true, 'auto_inc' => false],
            'role_id' => ['row_check' => true, 'auto_inc' => false],
            'domain_id' => ['row_check' => true, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            user_id     VARCHAR(50) NOT NULL,
            role_id     VARCHAR(50) NOT NULL,
            domain_id   VARCHAR(50) NOT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (user_id, role_id, domain_id),
            CONSTRAINT fk_' . $this->table_name . '_' . NEL_USERS_TABLE . '
            FOREIGN KEY (user_id) REFERENCES ' . NEL_USERS_TABLE . ' (user_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk_' . $this->table_name . '_' . NEL_ROLES_TABLE . '
            FOREIGN KEY (role_id) REFERENCES ' . NEL_ROLES_TABLE . ' (role_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk_' . $this->table_name . '_' . NEL_DOMAIN_REGISTRY_TABLE . '
            FOREIGN KEY (domain_id) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
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