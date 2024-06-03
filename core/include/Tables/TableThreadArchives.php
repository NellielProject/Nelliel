<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableThreadArchives extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'thread_id' => 'integer',
        'thread_data' => 'string',
        'time_archived' => 'integer',
        'permanent' => 'boolean',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'thread_id' => PDO::PARAM_INT,
        'thread_data' => PDO::PARAM_STR,
        'time_archived' => PDO::PARAM_INT,
        'permanent' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = '_archives';
        $this->column_checks = [
            'thread_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'thread_data' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'time_added' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'permanent' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            thread_id       INTEGER NOT NULL,
            thread_data     ' . $this->sql_compatibility->textType('LONGTEXT') . ',
            time_archived   BIGINT NOT NULL,
            permanent       SMALLINT NOT NULL DEFAULT 0,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (thread_id)
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