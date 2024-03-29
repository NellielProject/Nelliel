<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableBans extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_BANS_TABLE;
        $this->column_types = [
            'ban_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'board_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'creator' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'ban_type' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'hashed_ip_address' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'ip_address' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_LOB],
            'hashed_subnet' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'range_start' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_LOB],
            'range_end' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_LOB],
            'visitor_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'reason' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'start_time' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'length' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'seen' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'appeal_allowed' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'ban_id' => ['row_check' => true, 'auto_inc' => true, 'update' => false],
            'board_id' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'creator' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'ban_type' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'hashed_ip_address' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'ip_address' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'hashed_subnet' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'range_start' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'range_end' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'visitor_id' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'reason' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'start_time' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'length' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'seen' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'appeal_allowed' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            ban_id              ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            board_id            VARCHAR(50) NOT NULL,
            creator             VARCHAR(50) DEFAULT NULL,
            ban_type            SMALLINT NOT NULL DEFAULT 0,
            hashed_ip_address   VARCHAR(128) DEFAULT NULL,
            ip_address          ' . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . ' DEFAULT NULL,
            hashed_subnet       VARCHAR(128) DEFAULT NULL,
            range_start         ' . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . ' DEFAULT NULL,
            range_end           ' . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . ' DEFAULT NULL,
            visitor_id          VARCHAR(128) DEFAULT NULL,
            reason              TEXT NOT NULL,
            start_time          BIGINT NOT NULL,
            length              BIGINT NOT NULL,
            seen                SMALLINT NOT NULL DEFAULT 0,
            appeal_allowed      SMALLINT NOT NULL DEFAULT 0,
            moar                ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (ban_id),
            CONSTRAINT fk_bans__domain_registry
            FOREIGN KEY (board_id) REFERENCES ' . NEL_DOMAIN_REGISTRY_TABLE . ' (domain_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk_bans__users
            FOREIGN KEY (creator) REFERENCES ' . NEL_USERS_TABLE . ' (username)
            ON UPDATE CASCADE
            ON DELETE SET NULL,
            CONSTRAINT fk_bans__ip_info
            FOREIGN KEY (hashed_ip_address) REFERENCES ' . NEL_IP_INFO_TABLE . ' (hashed_ip_address)
            ON UPDATE CASCADE
            ON DELETE SET NULL,
            CONSTRAINT fk_bans__visitor_info
            FOREIGN KEY (visitor_id) REFERENCES ' . NEL_VISITOR_INFO_TABLE . ' (visitor_id)
            ON UPDATE CASCADE
            ON DELETE SET NULL
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
        $this->database->query('CREATE INDEX ix_nelliel_bans__hashed_ip_address ON ' . $this->table_name . ' (hashed_ip_address)');
    }

    public function insertDefaults()
    {
    }
}