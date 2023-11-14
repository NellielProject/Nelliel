<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableIPNotes extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'note_id' => 'integer',
        'username' => 'string',
        'hashed_ip_address' => 'string',
        'time' => 'integer',
        'notes' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'note_id' => PDO::PARAM_INT,
        'username' => PDO::PARAM_STR,
        'hashed_ip_address' => PDO::PARAM_STR,
        'time' => PDO::PARAM_INT,
        'notes' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_IP_NOTES_TABLE;
        $this->column_checks = [
            'note_id' => ['row_check' => false, 'auto_inc' => true, 'update' => false],
            'username' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'hashed_ip_address' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'time' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'notes' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            note_id             ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            username            VARCHAR(50) DEFAULT NULL,
            hashed_ip_address   VARCHAR(128) NOT NULL,
            time                BIGINT NOT NULL,
            notes               TEXT NOT NULL,
            moar                ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (note_id),
            CONSTRAINT fk_ip_notes__users
            FOREIGN KEY (username) REFERENCES ' . NEL_USERS_TABLE . ' (username)
            ON UPDATE CASCADE
            ON DELETE SET NULL
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