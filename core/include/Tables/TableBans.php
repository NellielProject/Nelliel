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
            'ip_type' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'ip_address_start' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_LOB],
            'ip_address_end' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_LOB],
            'hashed_ip_address' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'reason' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'start_time' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'length' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'seen' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'appeal' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'appeal_response' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'appeal_status' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'ban_id' => ['row_check' => true, 'auto_inc' => true],
            'board_id' => ['row_check' => false, 'auto_inc' => false],
            'creator' => ['row_check' => false, 'auto_inc' => false],
            'ip_type' => ['row_check' => false, 'auto_inc' => false],
            'ip_address_start' => ['row_check' => false, 'auto_inc' => false],
            'ip_address_end' => ['row_check' => false, 'auto_inc' => false],
            'hashed_ip_address' => ['row_check' => false, 'auto_inc' => false],
            'reason' => ['row_check' => false, 'auto_inc' => false],
            'start_time' => ['row_check' => false, 'auto_inc' => false],
            'length' => ['row_check' => false, 'auto_inc' => false],
            'seen' => ['row_check' => false, 'auto_inc' => false],
            'appeal' => ['row_check' => false, 'auto_inc' => false],
            'appeal_response' => ['row_check' => false, 'auto_inc' => false],
            'appeal_status' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            ban_id              " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            board_id            VARCHAR(50) NOT NULL,
            creator             VARCHAR(50) NOT NULL,
            ip_type             SMALLINT NOT NULL DEFAULT 0,
            ip_address_start    " . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            ip_address_end      " . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            hashed_ip_address   VARCHAR(128) DEFAULT NULL,
            reason              TEXT NOT NULL,
            start_time          BIGINT NOT NULL,
            length              BIGINT NOT NULL,
            seen                SMALLINT NOT NULL DEFAULT 0,
            appeal              TEXT DEFAULT NULL,
            appeal_response     TEXT DEFAULT NULL,
            appeal_status       SMALLINT NOT NULL DEFAULT 0,
            moar                TEXT DEFAULT NULL,
            CONSTRAINT fk1_" . $this->table_name . "_" . NEL_DOMAIN_REGISTRY_TABLE . "
            FOREIGN KEY (board_id) REFERENCES " . NEL_DOMAIN_REGISTRY_TABLE . " (domain_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
        $this->database->query('CREATE INDEX ip_address_start ON ' . $this->table_name . ' (ip_address_start)');
        $this->database->query('CREATE INDEX hashed_ip_address ON ' . $this->table_name . ' (hashed_ip_address)');
    }

    public function insertDefaults()
    {
    }
}