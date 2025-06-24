<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableStatistics extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'domain_id' => 'string',
        'statistic' => 'string',
        'value' => 'integer',
        'last_updated' => 'integer',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'domain_id' => PDO::PARAM_STR,
        'statistic' => PDO::PARAM_STR,
        'value' => PDO::PARAM_INT,
        'last_updated' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_STATISTICS_TABLE;
        $this->column_checks = [
            'domain_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'statistic' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'value' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'last_updated' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            domain_id       VARCHAR(50) NOT NULL,
            statistic       VARCHAR(50) NOT NULL,
            value           BIGINT NOT NULL,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (domain_id, statistic),
            CONSTRAINT fk_statistics__domain_registry
            FOREIGN KEY (domain_id) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
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