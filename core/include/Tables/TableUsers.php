<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableUsers extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'username' => 'string',
        'display_name' => 'string',
        'password' => 'string',
        'active' => 'boolean',
        'owner' => 'boolean',
        'last_login' => 'integer',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'username' => PDO::PARAM_STR,
        'display_name' => PDO::PARAM_STR,
        'password' => PDO::PARAM_STR,
        'active' => PDO::PARAM_INT,
        'owner' => PDO::PARAM_INT,
        'last_login' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_USERS_TABLE;
        $this->column_checks = [
            'username' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'display_name' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'password' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'active' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'owner' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'last_login' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            username        VARCHAR(50) NOT NULL,
            display_name    VARCHAR(255) NOT NULL,
            password        VARCHAR(255) NOT NULL,
            active          SMALLINT NOT NULL DEFAULT 0,
            owner           SMALLINT NOT NULL DEFAULT 0,
            last_login      BIGINT NOT NULL DEFAULT 0,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (username)
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