<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableVersions extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'id' => 'string',
        'type' => 'string',
        'original' => 'integer',
        'current' => 'integer'];

    public const PDO_TYPES = [
        'id' => PDO::PARAM_STR,
        'type' => PDO::PARAM_STR,
        'original' => PDO::PARAM_INT,
        'current' => PDO::PARAM_INT];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_VERSIONS_TABLE;
        $this->column_checks = [
            'id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'type' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'original' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'current' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            id          VARCHAR(50) NOT NULL,
            type        VARCHAR(50) NOT NULL,
            original    SMALLINT NOT NULL DEFAULT 0,
            current     SMALLINT NOT NULL DEFAULT 0,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (id, type)
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