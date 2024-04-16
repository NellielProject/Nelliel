<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableReports extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'report_id' => 'integer',
        'board_id' => 'string',
        'content_id' => 'string',
        'hashed_reporter_ip' => 'string',
        'reporter_ip' => 'string',
        'visitor_id' => 'string',
        'reason' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'report_id' => PDO::PARAM_INT,
        'board_id' => PDO::PARAM_STR,
        'content_id' => PDO::PARAM_STR,
        'hashed_reporter_ip' => PDO::PARAM_STR,
        'reporter_ip' => PDO::PARAM_LOB,
        'visitor_id' => PDO::PARAM_STR,
        'reason' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_REPORTS_TABLE;
        $this->column_checks = [
            'report_id' => ['row_check' => false, 'auto_inc' => true, 'update' => false],
            'board_id' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'content_id' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'hashed_reporter_ip' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'reporter_ip' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'visitor_id' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'reason' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            report_id           ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            board_id            VARCHAR(50) NOT NULL,
            content_id          VARCHAR(255) NOT NULL,
            hashed_reporter_ip  VARCHAR(128) DEFAULT NULL,
            reporter_ip         ' . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . ' DEFAULT NULL,
            visitor_id          VARCHAR(128) DEFAULT NULL,
            reason              TEXT NOT NULL,
            moar                ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (report_id),
            CONSTRAINT fk_reports__domain_registry
            FOREIGN KEY (board_id) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk_reports__ip_info
            FOREIGN KEY (hashed_reporter_ip) REFERENCES ' . NEL_IP_INFO_TABLE . ' (hashed_ip_address)
            ON UPDATE CASCADE
            ON DELETE SET NULL,
            CONSTRAINT fk_reports__visitor_info
            FOREIGN KEY (visitor_id) REFERENCES ' . NEL_VISITOR_INFO_TABLE . ' (visitor_id)
            ON UPDATE CASCADE
            ON DELETE SET NULL
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