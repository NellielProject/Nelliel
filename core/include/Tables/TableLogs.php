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
        $this->table_name = NEL_LOGS_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'level' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'domain_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'event_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'originator' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'ip_address' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_LOB],
            'hashed_ip_address' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'time' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'message' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'level' => ['row_check' => false, 'auto_inc' => false],
            'domain_id' => ['row_check' => false, 'auto_inc' => false],
            'event_id' => ['row_check' => false, 'auto_inc' => false],
            'originator' => ['row_check' => false, 'auto_inc' => false],
            'ip_address' => ['row_check' => false, 'auto_inc' => false],
            'hashed_ip_address' => ['row_check' => false, 'auto_inc' => false],
            'time' => ['row_check' => false, 'auto_inc' => false],
            'message' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry               " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            level               INTEGER NOT NULL,
            domain_id           VARCHAR(50) NOT NULL,
            event_id            VARCHAR(50) NOT NULL,
            originator          VARCHAR(50) NOT NULL,
            ip_address          " . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            hashed_ip_address   VARCHAR(128) DEFAULT NULL,
            time                BIGINT NOT NULL,
            message             TEXT NOT NULL,
            moar                TEXT DEFAULT NULL,
            CONSTRAINT fk_" . $this->table_name . "_" . NEL_DOMAIN_REGISTRY_TABLE . "
            FOREIGN KEY (domain_id) REFERENCES " . NEL_DOMAIN_REGISTRY_TABLE . " (domain_id)
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