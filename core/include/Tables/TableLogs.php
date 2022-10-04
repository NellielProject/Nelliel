<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableLogs extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_SYSTEM_LOGS_TABLE;
        $this->column_types = [
            'log_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'level' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'event' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'message' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'message_values' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'time' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'domain_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'username' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'ip_address' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_LOB],
            'hashed_ip_address' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'visitor_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'log_id' => ['row_check' => false, 'auto_inc' => true],
            'level' => ['row_check' => false, 'auto_inc' => false],
            'event' => ['row_check' => false, 'auto_inc' => false],
            'message' => ['row_check' => false, 'auto_inc' => false],
            'message_values' => ['row_check' => false, 'auto_inc' => false],
            'time' => ['row_check' => false, 'auto_inc' => false],
            'domain_id' => ['row_check' => false, 'auto_inc' => false],
            'username' => ['row_check' => false, 'auto_inc' => false],
            'ip_address' => ['row_check' => false, 'auto_inc' => false],
            'hashed_ip_address' => ['row_check' => false, 'auto_inc' => false],
            'visitor_id' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            log_id              ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            level               SMALLINT NOT NULL,
            event               VARCHAR(50) NOT NULL,
            message             TEXT NOT NULL,
            message_values      TEXT NOT NULL,
            time                BIGINT NOT NULL,
            domain_id           VARCHAR(50) NOT NULL,
            username            VARCHAR(50) DEFAULT NULL,
            ip_address          ' . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . ' DEFAULT NULL,
            hashed_ip_address   VARCHAR(128) DEFAULT NULL,
            visitor_id          VARCHAR(128) DEFAULT NULL,
            moar                TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (log_id),
            CONSTRAINT fk_logs__domain_registry
            FOREIGN KEY (domain_id) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk_logs__users
            FOREIGN KEY (username) REFERENCES ' . NEL_USERS_TABLE . ' (username)
            ON UPDATE CASCADE
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