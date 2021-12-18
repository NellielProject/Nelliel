<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableNews extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_NEWS_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'user_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'name' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'time' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'headline' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'text' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'user_id' => ['row_check' => false, 'auto_inc' => false],
            'name' => ['row_check' => false, 'auto_inc' => false],
            'time' => ['row_check' => false, 'auto_inc' => false],
            'headline' => ['row_check' => false, 'auto_inc' => false],
            'text' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry       " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            user_id     VARCHAR(50) NOT NULL,
            name        VARCHAR(255) NOT NULL,
            time        BIGINT NOT NULL,
            headline    VARCHAR(255) NOT NULL,
            text        TEXT NOT NULL,
            moar        TEXT DEFAULT NULL,
            CONSTRAINT fk_" . $this->table_name . "_" . NEL_USERS_TABLE . "
            FOREIGN KEY (user_id) REFERENCES " . NEL_USERS_TABLE . " (user_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
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