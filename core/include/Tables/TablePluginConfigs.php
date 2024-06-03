<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TablePluginConfigs extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'plugin_id' => 'string',
        'board_id' => 'string',
        'setting_name' => 'string',
        'setting_value' => 'string',
        'stored_raw' => 'boolean',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'plugin_id' => PDO::PARAM_STR,
        'board_id' => PDO::PARAM_STR,
        'setting_name' => PDO::PARAM_STR,
        'setting_value' => PDO::PARAM_STR,
        'stored_raw' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];


    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_PLUGIN_CONFIGS_TABLE;
        $this->column_checks = [
            'plugin_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'board_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
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
            plugin_id       VARCHAR(50) NOT NULL,
            board_id        VARCHAR(50) NOT NULL,
            setting_name    VARCHAR(50) NOT NULL,
            setting_value   ' . $this->sql_compatibility->textType('LONGTEXT') . ' NOT NULL,
            stored_raw      SMALLINT NOT NULL DEFAULT 0,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (plugin_id, board_id, setting_name),
            CONSTRAINT fk_plugin_configs__plugins
            FOREIGN KEY (plugin_id) REFERENCES ' . NEL_PLUGINS_TABLE . ' (plugin_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk_plugin_configs__domain_registry
            FOREIGN KEY (board_id) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
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