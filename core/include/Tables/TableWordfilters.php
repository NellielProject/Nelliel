<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableWordfilters extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'filter_id' => 'integer',
        'board_id' => 'string',
        'text_match' => 'string',
        'replacement' => 'string',
        'filter_action' => 'string',
        'notes' => 'string',
        'enabled' => 'boolean',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'filter_id' => PDO::PARAM_INT,
        'board_id' => PDO::PARAM_STR,
        'text_match' => PDO::PARAM_STR,
        'replacement' => PDO::PARAM_STR,
        'filter_action' => PDO::PARAM_STR,
        'notes' => PDO::PARAM_STR,
        'enabled' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_WORDFILTERS_TABLE;
        $this->column_checks = [
            'filter_id' => ['row_check' => false, 'auto_inc' => true, 'update' => false],
            'board_id' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'text_match' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'replacement' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'filter_action' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'notes' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            filter_id       ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            board_id        VARCHAR(50) DEFAULT NULL,
            text_match      TEXT NOT NULL,
            replacement     TEXT NOT NULL,
            filter_action   VARCHAR(255) DEFAULT NULL,
            notes           TEXT DEFAULT NULL,
            enabled         SMALLINT NOT NULL DEFAULT 0,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (filter_id),
            CONSTRAINT fk_wordfilters__domain_registry
            FOREIGN KEY (board_id) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
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