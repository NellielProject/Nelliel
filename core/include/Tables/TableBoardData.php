<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableBoardData extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'board_id' => 'string',
        'db_prefix' => 'string',
        'source_directory' => 'string',
        'preview_directory' => 'string',
        'page_directory' => 'string',
        'archive_directory' => 'string',
        'locked' => 'boolean',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'board_id' => PDO::PARAM_STR,
        'db_prefix' => PDO::PARAM_STR,
        'source_directory' => PDO::PARAM_STR,
        'preview_directory' => PDO::PARAM_STR,
        'page_directory' => PDO::PARAM_STR,
        'archive_directory' => PDO::PARAM_STR,
        'locked' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_BOARD_DATA_TABLE;
        $this->column_checks = [
            'board_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'db_prefix' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'source_directory' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'preview_directory' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'page_directory' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'archive_directory' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'locked' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            board_id            VARCHAR(50) NOT NULL,
            db_prefix           VARCHAR(20) NOT NULL,
            source_directory    VARCHAR(255) NOT NULL,
            preview_directory   VARCHAR(255) NOT NULL,
            page_directory      VARCHAR(255) NOT NULL,
            archive_directory   VARCHAR(255) NOT NULL,
            locked              SMALLINT NOT NULL DEFAULT 0,
            moar                ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (board_id),
            CONSTRAINT uc_db_prefix UNIQUE (db_prefix),
            CONSTRAINT fk_board_data__domain_registry
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