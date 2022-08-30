<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableBanAppeals extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_BAN_APPEALS_TABLE;
        $this->column_types = [
            'appeal_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'ban_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_INT],
            'time' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_INT],
            'appeal' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_STR],
            'response' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'pending' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_INT],
            'denied' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_INT]];
        $this->column_checks = [
            'appeal_id' => ['row_check' => true, 'auto_inc' => true],
            'ban_id' => ['row_check' => false, 'auto_inc' => false],
            'time' => ['row_check' => false, 'auto_inc' => false],
            'appeal' => ['row_check' => false, 'auto_inc' => false],
            'response' => ['row_check' => false, 'auto_inc' => false],
            'pending' => ['row_check' => false, 'auto_inc' => false],
            'denied' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            appeal_id   ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            ban_id      INTEGER NOT NULL DEFAULT 0,
            time        BIGINT NOT NULL,
            appeal      TEXT DEFAULT NULL,
            response    TEXT DEFAULT NULL,
            pending     SMALLINT NOT NULL DEFAULT 0,
            denied      SMALLINT NOT NULL DEFAULT 0,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (appeal_id),
            CONSTRAINT fk_ban_appeals__bans
            FOREIGN KEY (ban_id) REFERENCES ' . NEL_BANS_TABLE . ' (ban_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) ' . $options . ';';

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