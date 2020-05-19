<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableAssets extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_ASSETS_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'asset_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'type' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'is_default' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'info' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            asset_id        VARCHAR(50) NOT NULL,
            type            VARCHAR(50) NOT NULL,
            is_default      SMALLINT NOT NULL DEFAULT 0,
            info            TEXT NOT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['icons-nelliel-basic', 'icon-set', 1, '{"id": "icons-nelliel-basic","directory": "nelliel_basic","name": "Nelliel Basic Icon Set","version": "1.0","description": "The basic icon set for Nelliel."}']);
        $this->insertDefaultRow(['style-nelliel', 'style', 1, '{"id": "style-nelliel","directory": "nelliel","main_file": "nelliel.css","name": "Nelliel","version": "1.0","description": "Nelliel style","style_type": "css"}']);
        $this->insertDefaultRow(['style-nelliel-b', 'style', 0, '{"id": "style-nelliel-b","directory": "nelliel_b","main_file": "nelliel_b.css","name": "Nelliel B","version": "1.0","description": "Nelliel B style","style_type": "css"}']);
        $this->insertDefaultRow(['style-futaba', 'style', 0, '{"id": "style-futaba","directory": "futaba","main_file": "futaba.css","name": "Futaba","version": "1.0","description": "Futaba style","style_type": "css"}']);
        $this->insertDefaultRow(['style-burichan', 'style', 0, '{"id": "style-burichan","directory": "burichan","main_file": "burichan.css","name": "Burichan","version": "1.0","description": "Burichan style","style_type": "css"}']);
        $this->insertDefaultRow(['style-nigra', 'style', 0, '{"id": "style-nigra","directory": "nigra","main_file": "nigra.css","name": "Nigra","version": "1.0","description": "Nigra style","style_type": "css"}']);
    }
}