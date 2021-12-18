<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableCaptcha extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_CAPTCHA_TABLE;
        $this->columns_data = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'captcha_key' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'captcha_text' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'domain_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'time_created' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'seen' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'solved' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->columns_data = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'captcha_key' => ['row_check' => true, 'auto_inc' => false],
            'captcha_text' => ['row_check' => false, 'auto_inc' => false],
            'domain_id' => ['row_check' => false, 'auto_inc' => false],
            'time_created' => ['row_check' => false, 'auto_inc' => false],
            'seen' => ['row_check' => false, 'auto_inc' => false],
            'solved' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            captcha_key     VARCHAR(100) NOT NULL,
            captcha_text    VARCHAR(255) NOT NULL,
            domain_id       VARCHAR(50) DEFAULT NULL,
            time_created    BIGINT NOT NULL,
            seen            SMALLINT DEFAULT 0,
            solved          SMALLINT DEFAULT 0,
            moar            TEXT DEFAULT NULL,
            CONSTRAINT uc_captcha_key UNIQUE (captcha_key),
            CONSTRAINT fk2_" . $this->table_name . "_" . NEL_DOMAIN_REGISTRY_TABLE . "
            FOREIGN KEY (domain_id) REFERENCES " . NEL_DOMAIN_REGISTRY_TABLE . " (domain_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
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