<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableIPInfo extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'info_id' => 'integer',
        'hashed_ip_address' => 'string',
        'ip_address' => 'string',
        'hashed_small_subnet' => 'string',
        'hashed_large_subnet' => 'string',
        'last_activity' => 'integer',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'info_id' => PDO::PARAM_INT,
        'hashed_ip_address' => PDO::PARAM_STR,
        'ip_address' => PDO::PARAM_STR,
        'hashed_small_subnet' => PDO::PARAM_STR,
        'hashed_large_subnet' => PDO::PARAM_STR,
        'last_activity' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_IP_INFO_TABLE;
        $this->column_checks = [
            'info_id' => ['row_check' => false, 'auto_inc' => true, 'update' => false],
            'hashed_ip_address' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'ip_address' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'hashed_small_subnet' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'hashed_large_subnet' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'last_activity' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            info_id             ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            hashed_ip_address   VARCHAR(128) NOT NULL,
            ip_address          ' . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . ' DEFAULT NULL,
            hashed_small_subnet VARCHAR(128) DEFAULT NULL,
            hashed_large_subnet VARCHAR(128) DEFAULT NULL,
            last_activity       BIGINT NOT NULL DEFAULT 0,
            moar                ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (info_id),
            CONSTRAINT uc_hashed_ip_address UNIQUE (hashed_ip_address),
            CONSTRAINT uc_ip_address UNIQUE (ip_address)
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