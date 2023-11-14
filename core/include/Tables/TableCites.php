<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableCites extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'cite_id' => 'integer',
        'source_board' => 'string',
        'source_post' => 'integer',
        'target_board' => 'string',
        'target_post' => 'integer'];

    public const PDO_TYPES = [
        'cite_id' => PDO::PARAM_INT,
        'source_board' => PDO::PARAM_STR,
        'source_post' => PDO::PARAM_INT,
        'target_board' => PDO::PARAM_STR,
        'target_post' => PDO::PARAM_INT];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_CITES_TABLE;
        $this->column_checks = [
            'cite_id' => ['row_check' => false, 'auto_inc' => true, 'update' => false],
            'source_board' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'source_post' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'target_board' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'target_post' => ['row_check' => true, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            cite_id         ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            source_board    VARCHAR(50) NOT NULL,
            source_post     INTEGER NOT NULL DEFAULT 0,
            target_board    VARCHAR(50) NOT NULL,
            target_post     INTEGER NOT NULL DEFAULT 0,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (cite_id),
            CONSTRAINT fk_cites__domain_registry
            FOREIGN KEY (source_board) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk2_cites__domain_registry
            FOREIGN KEY (target_board) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
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