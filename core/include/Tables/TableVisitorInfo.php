<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableVisitorInfo extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'visitor_id' => 'string',
        'last_activity' => 'integer',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'visitor_id' => PDO::PARAM_STR,
        'last_activity' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_VISITOR_INFO_TABLE;
        $this->column_checks= [
            'visitor_id' => ['row_check' => false, 'auto_inc' => true, 'update' => false],
            'last_activity' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            visitor_id      VARCHAR(255) NOT NULL,
            last_activity   BIGINT NOT NULL DEFAULT 0,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (visitor_id)
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