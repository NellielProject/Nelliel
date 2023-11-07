<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableScripts extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_SCRIPTS_TABLE;
        $this->column_types = [
            'script_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'label' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'location' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'full_url' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'enabled' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'notes' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'script_id' => ['row_check' => true, 'auto_inc' => true, 'update' => false],
            'label' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'location' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'full_url' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'notes' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            script_id   ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            label       VARCHAR(255) NOT NULL,
            location    TEXT NOT NULL,
            full_url    SMALLINT NOT NULL DEFAULT 0,
            enabled     SMALLINT NOT NULL DEFAULT 0,
            notes       TEXT DEFAULT NULL,
            moar        ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (script_id)
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['Nelliel Main', 'core/nelliel.js', 0, 1, 'Main script for Nelliel.']);
        $this->insertDefaultRow(['Nelliel Functions', 'core/functions.js', 0, 1, 'Has various core functions.']);
        $this->insertDefaultRow(['Nelliel UI', 'core/ui.js', 0, 1, 'Handles UI interactions.']);
    }
}