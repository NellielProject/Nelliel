<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableCache extends Table
{
    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_CACHE_TABLE;
        $this->columns_data = [
            'domain_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'cache_key' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'cache_data' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'regen' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->columns_data = [
            'domain_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'cache_key' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'cache_data' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'regen' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false, 'update' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            domain_id       VARCHAR(50) NOT NULL,
            cache_key       VARCHAR(50) NOT NULL,
            cache_data      TEXT NOT NULL,
            regen           SMALLINT NOT NULL DEFAULT 0,
            moar            TEXT DEFAULT NULL,
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