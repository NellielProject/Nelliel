<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableRoles extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'role_id' => 'string',
        'role_level' => 'integer',
        'role_title' => 'string',
        'capcode' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'role_id' => PDO::PARAM_STR,
        'role_level' => PDO::PARAM_INT,
        'role_title' => PDO::PARAM_STR,
        'capcode' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_ROLES_TABLE;
        $this->column_checks = [
            'role_id' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'role_level' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'role_title' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'capcode' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            role_id         VARCHAR(50) NOT NULL,
            role_level      SMALLINT NOT NULL DEFAULT 0,
            role_title      VARCHAR(255) NOT NULL,
            capcode         VARCHAR(255) DEFAULT NULL,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (role_id)
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['site_admin', 100, 'Site Administrator', 'Site Administrator']);
        $this->insertDefaultRow(['board_owner', 75, 'Board Owner', 'Board Owner']);
        $this->insertDefaultRow(['moderator', 50, 'Moderator', 'Moderator']);
        $this->insertDefaultRow(['janitor', 25, 'Janitor', 'Janitor']);
        $this->insertDefaultRow(['basic_user', 0, 'Basic', '']);
    }
}