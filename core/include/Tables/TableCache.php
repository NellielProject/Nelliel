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
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'domain_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'cache_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'cache_data' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'regen' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->columns_data = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'domain_id' => ['row_check' => true, 'auto_inc' => false],
            'cache_id' => ['row_check' => true, 'auto_inc' => false],
            'cache_data' => ['row_check' => false, 'auto_inc' => false],
            'regen' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            domain_id       VARCHAR(50) NOT NULL,
            cache_id        VARCHAR(50) NOT NULL,
            cache_data      TEXT NOT NULL,
            regen           SMALLINT NOT NULL DEFAULT 0,
            moar            TEXT DEFAULT NULL,
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