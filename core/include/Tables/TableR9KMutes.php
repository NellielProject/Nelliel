<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableR9KMutes extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'mute_id' => 'string',
        'board_id' => 'string',
        'poster_hash' => 'string',
        'mute_time' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'mute_id' => PDO::PARAM_INT,
        'board_id' => PDO::PARAM_STR,
        'poster_hash' => PDO::PARAM_STR,
        'mute_time' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_R9K_MUTES_TABLE;
        $this->column_checks = [
            'mute_id' => ['row_check' => true, 'auto_inc' => true, 'update' => false],
            'board_id' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'poster_hash' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'mute_time' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('BIGINT', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            mute_id         ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            board_id        VARCHAR(50) NOT NULL,
            poster_hash     VARCHAR(128) NOT NULL,
            mute_time       BIGINT NOT NULL,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (mute_id),
            CONSTRAINT fk_r9k_mutes__domain_registry
            FOREIGN KEY (board_id) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
        $this->database->query('CREATE INDEX ix_' . $this->table_name . '__poster_board ON ' . $this->table_name . ' (poster_hash, board_id)');
    }

    public function insertDefaults()
    {
    }
}