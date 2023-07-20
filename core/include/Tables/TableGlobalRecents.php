<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableGlobalRecents extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_GLOBAL_RECENTS_TABLE;
        $this->column_types = [
            'content_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'board_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'post_time' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'post_time_milli' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'content_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false, 'update' => false],
            'board_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false, 'update' => false],
            'post_time' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false, 'update' => false],
            'post_time_milli' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false, 'update' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            content_id          INTEGER NOT NULL,
            board_id            VARCHAR(50) NOT NULL,
            post_time           BIGINT NOT NULL,
            post_time_milli     SMALLINT NOT NULL,
            moar                ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (content_id, board_id),
            CONSTRAINT fk_recents__domain_registry
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