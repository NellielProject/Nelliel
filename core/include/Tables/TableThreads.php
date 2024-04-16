<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableThreads extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'thread_id' => 'integer',
        'bump_time' => 'integer',
        'bump_time_milli' => 'integer',
        'last_update' => 'integer',
        'last_update_milli' => 'integer',
        'post_count' => 'integer',
        'bump_count' => 'integer',
        'total_uploads' => 'integer',
        'file_count' => 'integer',
        'embed_count' => 'integer',
        'permasage' => 'boolean',
        'sticky' => 'boolean',
        'cyclic' => 'boolean',
        'old' => 'boolean',
        'preserve' => 'boolean',
        'locked' => 'boolean',
        'shadow' => 'boolean',
        'slug' => 'string',
        'salt' => 'string',
        'regen_cache' => 'boolean',
        'cache' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'thread_id' => PDO::PARAM_INT,
        'bump_time' => PDO::PARAM_INT,
        'bump_time_milli' => PDO::PARAM_INT,
        'last_update' => PDO::PARAM_INT,
        'last_update_milli' => PDO::PARAM_INT,
        'post_count' => PDO::PARAM_INT,
        'bump_count' => PDO::PARAM_INT,
        'total_uploads' => PDO::PARAM_INT,
        'file_count' => PDO::PARAM_INT,
        'embed_count' => PDO::PARAM_INT,
        'permasage' => PDO::PARAM_INT,
        'sticky' => PDO::PARAM_INT,
        'cyclic' => PDO::PARAM_INT,
        'old' => PDO::PARAM_INT,
        'preserve' => PDO::PARAM_INT,
        'locked' => PDO::PARAM_INT,
        'shadow' => PDO::PARAM_INT,
        'slug' => PDO::PARAM_STR,
        'salt' => PDO::PARAM_STR,
        'regen_cache' => PDO::PARAM_INT,
        'cache' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = '_threads';
        $this->column_checks = [
            'thread_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'bump_time' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'bump_time_milli' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'last_update' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'last_update_milli' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'post_count' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'bump_count' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'total_uploads' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'file_count' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'embed_count' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'permasage' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'sticky' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'cyclic' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'old' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'preserve' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'locked' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'shadow' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'slug' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'salt' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'regen_cache' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'cache' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
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
            moar                ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
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