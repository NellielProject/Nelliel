<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableThreads extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = '_threads';
        $this->column_types = [
            'thread_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'bump_time' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'bump_time_milli' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'last_update' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'last_update_milli' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'post_count' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'bump_count' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'total_uploads' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'file_count' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'embed_count' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'permasage' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'sticky' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'cyclic' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'old' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'preserve' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'locked' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'shadow' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'slug' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'salt' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'regen_cache' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'cache' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'thread_id' => ['row_check' => true, 'auto_inc' => false],
            'bump_time' => ['row_check' => false, 'auto_inc' => false],
            'bump_time_milli' => ['row_check' => false, 'auto_inc' => false],
            'last_update' => ['row_check' => false, 'auto_inc' => false],
            'last_update_milli' => ['row_check' => false, 'auto_inc' => false],
            'post_count' => ['row_check' => false, 'auto_inc' => false],
            'bump_count' => ['row_check' => false, 'auto_inc' => false],
            'total_uploads' => ['row_check' => false, 'auto_inc' => false],
            'file_count' => ['row_check' => false, 'auto_inc' => false],
            'embed_count' => ['row_check' => false, 'auto_inc' => false],
            'permasage' => ['row_check' => false, 'auto_inc' => false],
            'sticky' => ['row_check' => false, 'auto_inc' => false],
            'cyclic' => ['row_check' => false, 'auto_inc' => false],
            'old' => ['row_check' => false, 'auto_inc' => false],
            'preserve' => ['row_check' => false, 'auto_inc' => false],
            'locked' => ['row_check' => false, 'auto_inc' => false],
            'shadow' => ['row_check' => false, 'auto_inc' => false],
            'slug' => ['row_check' => false, 'auto_inc' => false],
            'salt' => ['row_check' => false, 'auto_inc' => false],
            'regen_cache' => ['row_check' => false, 'auto_inc' => false],
            'cache' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            thread_id           INTEGER NOT NULL,
            bump_time           BIGINT NOT NULL,
            bump_time_milli     SMALLINT NOT NULL,
            last_update         BIGINT NOT NULL,
            last_update_milli   SMALLINT NOT NULL,
            post_count          INTEGER NOT NULL DEFAULT 0,
            bump_count          INTEGER NOT NULL DEFAULT 0,
            total_uploads       INTEGER NOT NULL DEFAULT 0,
            file_count          INTEGER NOT NULL DEFAULT 0,
            embed_count         INTEGER NOT NULL DEFAULT 0,
            permasage           SMALLINT NOT NULL DEFAULT 0,
            sticky              SMALLINT NOT NULL DEFAULT 0,
            cyclic              SMALLINT NOT NULL DEFAULT 0,
            old                 SMALLINT NOT NULL DEFAULT 0,
            preserve            SMALLINT NOT NULL DEFAULT 0,
            locked              SMALLINT NOT NULL DEFAULT 0,
            shadow              SMALLINT NOT NULL DEFAULT 0,
            slug                TEXT DEFAULT NULL,
            salt                VARCHAR(255) NOT NULL,
            regen_cache         SMALLINT NOT NULL DEFAULT 0,
            cache               TEXT DEFAULT NULL,
            moar                TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (thread_id)
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