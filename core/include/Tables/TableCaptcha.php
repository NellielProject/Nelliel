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
            'captcha_key' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'captcha_text' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'domain_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'time_created' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'seen' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'solved' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->columns_data = [
            'captcha_key' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'captcha_text' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'domain_id' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'time_created' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'seen' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'solved' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            captcha_key     VARCHAR(128) NOT NULL,
            captcha_text    VARCHAR(255) NOT NULL,
            domain_id       VARCHAR(50) DEFAULT NULL,
            time_created    BIGINT NOT NULL,
            seen            SMALLINT DEFAULT 0,
            solved          SMALLINT DEFAULT 0,
            moar            TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (captcha_key),
            CONSTRAINT fk_captcha__domain_registry
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