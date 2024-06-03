<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableBanAppeals extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'appeal_id' => 'integer',
        'ban_id' => 'string',
        'time' => 'string',
        'appeal' => 'integer',
        'response' => 'string',
        'pending' => 'string',
        'denied' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'appeal_id' => PDO::PARAM_INT,
        'ban_id' => PDO::PARAM_INT,
        'time' => PDO::PARAM_INT,
        'appeal' => PDO::PARAM_STR,
        'response' => PDO::PARAM_STR,
        'pending' => PDO::PARAM_INT,
        'denied' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_BAN_APPEALS_TABLE;
        $this->column_checks = [
            'appeal_id' => ['row_check' => true, 'auto_inc' => true, 'update' => false],
            'ban_id' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'time' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'appeal' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'response' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'pending' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'denied' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
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
            moar        ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
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
    }

    public function insertDefaults()
    {
    }
}