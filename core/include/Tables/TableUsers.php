<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableUsers extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_USERS_TABLE;
        $this->column_types = [
            'username' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'display_name' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'password' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'active' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'owner' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'last_login' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'username' => ['row_check' => true, 'auto_inc' => false],
            'display_name' => ['row_check' => false, 'auto_inc' => false],
            'password' => ['row_check' => false, 'auto_inc' => false],
            'active' => ['row_check' => false, 'auto_inc' => false],
            'owner' => ['row_check' => false, 'auto_inc' => false],
            'last_login' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            username        VARCHAR(50) NOT NULL,
            display_name    VARCHAR(255) NOT NULL,
            password        VARCHAR(255) NOT NULL,
            active          SMALLINT NOT NULL DEFAULT 0,
            owner           SMALLINT NOT NULL DEFAULT 0,
            last_login      BIGINT NOT NULL DEFAULT 0,
            moar            TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (username)
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