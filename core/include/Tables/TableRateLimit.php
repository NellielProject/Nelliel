<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableRateLimit extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'rate_id' => 'string',
        'record' => 'string'];

    public const PDO_TYPES = [
        'rate_id' => PDO::PARAM_LOB,
        'record' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_RATE_LIMIT_TABLE;
        $this->column_checks = [
            'rate_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'record' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            rate_id    VARCHAR(128) NOT NULL,
            record     TEXT NOT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (rate_id)
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