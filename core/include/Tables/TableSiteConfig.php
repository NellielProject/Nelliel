<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableSiteConfig extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'setting_name' => 'string',
        'setting_value' => 'string',
        'stored_raw' => 'boolean',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'setting_name' => PDO::PARAM_STR,
        'setting_value' => PDO::PARAM_STR,
        'stored_raw' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_SITE_CONFIG_TABLE;
        $this->column_checks = [
            'setting_name' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'setting_value' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'stored_raw' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            setting_name    VARCHAR(50) NOT NULL,
            setting_value   ' . $this->sql_compatibility->textType('LONGTEXT') . ' NOT NULL,
            stored_raw      SMALLINT NOT NULL DEFAULT 0,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (setting_name)
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $query = 'SELECT "setting_name", "default_value" FROM "' . NEL_SETTINGS_TABLE .
        '" WHERE "setting_category" = \'site\'';
        $board_settings = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);

        foreach ($board_settings as $setting) {
            $this->insertDefaultRow([$setting['setting_name'], $setting['default_value']]);
        }
    }
}