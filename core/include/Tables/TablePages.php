<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TablePages extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_PAGES_TABLE;
        $this->columns_data = [
            'page_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'domain_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'uri' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'title' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'text' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'markup_type' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'show_link' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->columns_data = [
            'page_id' => ['row_check' => false, 'auto_inc' => true, 'update' => false],
            'domain_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'uri' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'title' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'text' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'markup_type' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'show_link' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false, 'update' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            page_id         ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            domain_id       VARCHAR(50) NOT NULL,
            uri             VARCHAR(255) NOT NULL,
            title           VARCHAR(255) NOT NULL,
            text            ' . $this->sql_compatibility->textType('LONGTEXT') . ' NOT NULL,
            markup_type     VARCHAR(50) NOT NULL,
            show_link       SMALLINT NOT NULL DEFAULT 0,
            moar            TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (page_id),
            CONSTRAINT uc_domain_id__uri UNIQUE (domain_id, uri),
            CONSTRAINT fk_' . $this->table_name . '_' . NEL_DOMAIN_REGISTRY_TABLE . '
            FOREIGN KEY (domain_id) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
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