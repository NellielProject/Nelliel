<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableR9KPosts extends Table
{
    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_R9K_POSTS_TABLE;
        $this->columns_data = [
            'board_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'post_hash' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'post_time' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->columns_data = [
            'board_id' => ['row_check' => true, 'auto_inc' => false],
            'post_hash' => ['row_check' => true, 'auto_inc' => false],
            'post_time' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            board_id    VARCHAR(50) NOT NULL,
            post_hash   VARCHAR(128) NOT NULL,
            post_time   BIGINT NOT NULL,
            moar        TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (board_id),
            CONSTRAINT fk_r9k_posts__domain_registry
            FOREIGN KEY (board_id) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
            ON UPDATE CASCADE
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
        $this->database->query('CREATE INDEX ix_' . $this->table_name . '__post_hash ON ' . $this->table_name . ' (post_hash)');
        $this->database->query('CREATE INDEX ix_' . $this->table_name . '__post_time ON ' . $this->table_name . ' (post_time)');
    }

    public function insertDefaults()
    {
    }
}