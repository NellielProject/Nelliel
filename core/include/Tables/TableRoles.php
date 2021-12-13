<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableRoles extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_ROLES_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'role_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'role_level' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'role_title' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'capcode_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'role_id' => ['row_check' => true, 'auto_inc' => false],
            'role_level' => ['row_check' => false, 'auto_inc' => false],
            'role_title' => ['row_check' => false, 'auto_inc' => false],
            'capcode_id' => ['row_check' => false, 'auto_inc' => false],
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
            role_id         VARCHAR(50) NOT NULL UNIQUE,
            role_level      SMALLINT NOT NULL DEFAULT 0,
            role_title      VARCHAR(255) NOT NULL,
            capcode_id      VARCHAR(255) NOT NULL,
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