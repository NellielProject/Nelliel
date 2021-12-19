<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableFiletypeCategories extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_FILETYPE_CATEGORIES_TABLE;
        $this->column_types = [
            'category' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'label' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'enabled' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'category' => ['row_check' => true, 'auto_inc' => false],
            'label' => ['row_check' => false, 'auto_inc' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            category        VARCHAR(50) NOT NULL,
            label           VARCHAR(255) NOT NULL,
            enabled         SMALLINT NOT NULL DEFAULT 0,
            moar            TEXT DEFAULT NULL,
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
        $this->insertDefaultRow(['other', 'Other', 1]);
    }
}