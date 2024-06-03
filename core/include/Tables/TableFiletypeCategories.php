<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableFiletypeCategories extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'category' => 'string',
        'label' => 'string',
        'enabled' => 'boolean',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'category' => PDO::PARAM_STR,
        'label' => PDO::PARAM_STR,
        'enabled' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_FILETYPE_CATEGORIES_TABLE;
        $this->column_checks = [
            'category' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'label' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            category        VARCHAR(50) NOT NULL,
            label           VARCHAR(255) NOT NULL,
            enabled         SMALLINT NOT NULL DEFAULT 0,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (category)
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['graphics', 'Graphics', 1]);
        $this->insertDefaultRow(['audio', 'Audio', 1]);
        $this->insertDefaultRow(['video', 'Video', 1]);
        $this->insertDefaultRow(['document', 'Document', 1]);
        $this->insertDefaultRow(['archive', 'Archive', 1]);
        $this->insertDefaultRow(['font', 'Font', 1]);
        $this->insertDefaultRow(['other', 'Other', 1]);
    }
}