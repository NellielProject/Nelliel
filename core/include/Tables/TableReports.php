<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableReports extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_REPORTS_TABLE;
        $this->column_types = [
            'report_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'board_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'content_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'reporter_ip' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_LOB],
            'hashed_reporter_ip' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'reason' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'report_id' => ['row_check' => false, 'auto_inc' => true],
            'board_id' => ['row_check' => false, 'auto_inc' => false],
            'content_id' => ['row_check' => false, 'auto_inc' => false],
            'reporter_ip' => ['row_check' => false, 'auto_inc' => false],
            'hashed_reporter_ip' => ['row_check' => false, 'auto_inc' => false],
            'reason' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            report_id           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            board_id            VARCHAR(50) NOT NULL,
            content_id          VARCHAR(255) NOT NULL,
            reporter_ip         " . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            hashed_reporter_ip  VARCHAR(128) NOT NULL,
            reason              TEXT NOT NULL,
            moar                TEXT DEFAULT NULL,
            CONSTRAINT fk1_" . $this->table_name . "_" . NEL_DOMAIN_REGISTRY_TABLE . "
            FOREIGN KEY (board_id) REFERENCES " . NEL_DOMAIN_REGISTRY_TABLE . " (domain_id)
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