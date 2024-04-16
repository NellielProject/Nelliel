<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableBlotter extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'record_id' => 'integer',
        'time' => 'integer',
        'text' =>'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'record_id' => PDO::PARAM_INT,
        'time' => PDO::PARAM_INT,
        'text' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_BLOTTER_TABLE;
        $this->column_checks = [
            'record_id' => ['row_check' => true, 'auto_inc' => true, 'update' => false],
            'time' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'text' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            record_id   ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            time        BIGINT NOT NULL,
            text        TEXT NOT NULL,
            moar        ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (record_id)
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