<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableR9KContent extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'entry' => 'string',
        'board_id' => 'string',
        'content_hash' => 'string',
        'post_time' => 'boolean',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'entry' => PDO::PARAM_INT,
        'board_id' => PDO::PARAM_STR,
        'content_hash' => PDO::PARAM_STR,
        'post_time' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_R9K_CONTENT_TABLE;
        $this->column_checks = [
            'entry' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'board_id' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'content_hash' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'post_time' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('BIGINT', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            entry           ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            board_id        VARCHAR(50) NOT NULL,
            content_hash    VARCHAR(128) NOT NULL,
            post_time       BIGINT NOT NULL,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (entry),
            CONSTRAINT fk_r9k_content__domain_registry
            FOREIGN KEY (board_id) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
        $this->database->query('CREATE INDEX ix_' . $this->table_name . '__hash_board ON ' . $this->table_name . ' (content_hash, board_id)');
    }

    public function insertDefaults()
    {
    }
}