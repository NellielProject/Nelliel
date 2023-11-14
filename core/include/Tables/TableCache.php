<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableCache extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'domain_id' => 'string',
        'cache_key' => 'string',
        'cache_data' => 'string',
        'regen' => 'boolean',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'domain_id' => PDO::PARAM_STR,
        'cache_key' => PDO::PARAM_STR,
        'cache_data' => PDO::PARAM_STR,
        'regen' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_CACHE_TABLE;
        $this->column_checks = [
            'domain_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'cache_key' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'cache_data' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'regen' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            domain_id       VARCHAR(50) NOT NULL,
            cache_key       VARCHAR(50) NOT NULL,
            cache_data      ' . $this->sql_compatibility->textType('LONGTEXT') . ' NOT NULL,
            regen           SMALLINT NOT NULL DEFAULT 0,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (domain_id, cache_key),
            CONSTRAINT fk_cache__domain_registry
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