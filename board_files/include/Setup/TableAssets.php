<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableAssets extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = ASSETS_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'type' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'is_default' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'info' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function setup()
    {
        $this->createTable();
        $this->insertDefaults();
    }

    public function createTable(array $other_tables = null)
    {
        $auto_inc = $this->sql_helpers->autoincrementColumn('INTEGER');
        $options = $this->sql_helpers->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            id              VARCHAR(255) NOT NULL,
            type            VARCHAR(255) NOT NULL,
            is_default      SMALLINT NOT NULL DEFAULT 0,
            info            TEXT NOT NULL
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['filetype-nelliel-basic', 'icon-set', 1, '{"id": "filetype-nelliel-basic","directory": "filetype_nelliel_basic","name": "Nelliel Basic Filetype Icon Set","set_type": "filetype","version": "1.0","description": "The basic filetype icon set for Nelliel."}']);
        $this->insertDefaultRow(['style-nelliel', 'style', 1, '{"id": "style-nelliel","directory": "nelliel","main_file": "nelliel.css","name": "Nelliel","version": "1.0","description": "Nelliel style","style_type": "css"}']);
        $this->insertDefaultRow(['style-nelliel-b', 'style', 0, '{"id": "style-nelliel-b","directory": "nelliel_b","main_file": "nelliel_b.css","name": "Nelliel B","version": "1.0","description": "Nelliel B style","style_type": "css"}']);
        $this->insertDefaultRow(['style-futaba', 'style', 0, '{"id": "style-futaba","directory": "futaba","main_file": "futaba.css","name": "Futaba","version": "1.0","description": "Futaba style","style_type": "css"}']);
        $this->insertDefaultRow(['style-burichan', 'style', 0, '{"id": "style-burichan","directory": "burichan","main_file": "burichan.css","name": "Burichan","version": "1.0","description": "Burichan style","style_type": "css"}']);
        $this->insertDefaultRow(['style-nigra', 'style', 0, '{"id": "style-nigra","directory": "nigra","main_file": "nigra.css","name": "Nigra","version": "1.0","description": "Nigra style","style_type": "css"}']);
    }
}