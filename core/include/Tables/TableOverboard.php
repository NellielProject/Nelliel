<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableOverboard extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'overboard_id' => 'string',
        'thread_id' => 'string',
        'board_id' => 'string',
        'bump_time' => 'integer',
        'bump_time_milli' => 'integer',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'overboard_id' => PDO::PARAM_STR,
        'thread_id' => PDO::PARAM_STR,
        'board_id' => PDO::PARAM_STR,
        'bump_time' => PDO::PARAM_INT,
        'bump_time_milli' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_OVERBOARD_TABLE;
        $this->column_checks = [
            'overboard_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false, 'update' => false],
            'thread_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false, 'update' => false],
            'board_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false, 'update' => false],
            'bump_time' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false, 'update' => false],
            'bump_time_milli' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            overboard_id        VARCHAR(50) NOT NULL,
            thread_id           INTEGER NOT NULL,
            board_id            VARCHAR(50) NOT NULL,
            bump_time           BIGINT NOT NULL,
            bump_time_milli     SMALLINT NOT NULL,
            moar                ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (overboard_id, thread_id, board_id),
            CONSTRAINT fk_overboard__domain_registry
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