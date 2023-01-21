<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableNoticeboard extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_NOTICEBOARD_TABLE;
        $this->column_types = [
            'notice_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'username' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'time' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'subject' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'message' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_types = [
            'notice_id' => ['row_check' => false, 'auto_inc' => true, 'update' => false],
            'username' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'time' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'subject' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'message' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            notice_id   ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            username    VARCHAR(50) NOT NULL,
            time        BIGINT NOT NULL,
            subject     TEXT NOT NULL,
            message     TEXT NOT NULL,
            moar        TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (notice_id),
            CONSTRAINT fk_noticeboard__users
            FOREIGN KEY (username) REFERENCES ' . NEL_USERS_TABLE . ' (username)
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