<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableImageSets extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_IMAGE_SETS_TABLE;
        $this->columns_data = [
            'set_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'directory' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'enabled' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT]];
        $this->columns_data = [
            'set_id' => ['row_check' => true, 'auto_inc' => false],
            'directory' => ['row_check' => false, 'auto_inc' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            set_id      VARCHAR(100) PRIMARY KEY NOT NULL,
            directory   VARCHAR(255) NOT NULL,
            enabled     SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
    }
}