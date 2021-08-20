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
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'user_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'display_name' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'user_password' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'hashed_user_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'active' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'locked' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'owner' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'last_login' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'user_id' => ['row_check' => true, 'auto_inc' => false],
            'display_name' => ['row_check' => false, 'auto_inc' => false],
            'user_password' => ['row_check' => false, 'auto_inc' => false],
            'hashed_user_id' => ['row_check' => false, 'auto_inc' => false],
            'active' => ['row_check' => false, 'auto_inc' => false],
            'locked' => ['row_check' => false, 'auto_inc' => false],
            'owner' => ['row_check' => false, 'auto_inc' => false],
            'last_login' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            user_id         VARCHAR(50) NOT NULL UNIQUE,
            display_name    VARCHAR(255) NOT NULL,
            user_password   VARCHAR(255) NOT NULL,
            hashed_user_id  VARCHAR(128) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL UNIQUE,
            active          SMALLINT NOT NULL DEFAULT 0,
            locked          SMALLINT NOT NULL DEFAULT 0,
            owner           SMALLINT NOT NULL DEFAULT 0,
            last_login      BIGINT NOT NULL DEFAULT 0,
            moar            TEXT DEFAULT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
    }
}