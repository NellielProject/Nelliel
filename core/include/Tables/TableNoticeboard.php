<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableNoticeboard extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'notice_id' => 'integer',
        'username' => 'string',
        'time' => 'integer',
        'subject' => 'string',
        'message' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'notice_id' => PDO::PARAM_INT,
        'username' => PDO::PARAM_STR,
        'time' => PDO::PARAM_INT,
        'subject' => PDO::PARAM_STR,
        'message' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_NOTICEBOARD_TABLE;
        $this->column_checks = [
            'notice_id' => ['row_check' => false, 'auto_inc' => true, 'update' => false],
            'username' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'time' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'subject' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'message' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            notice_id   ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            username    VARCHAR(50) DEFAULT NULL,
            time        BIGINT NOT NULL,
            subject     TEXT NOT NULL,
            message     TEXT NOT NULL,
            moar        ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (notice_id),
            CONSTRAINT fk_noticeboard__users
            FOREIGN KEY (username) REFERENCES ' . NEL_USERS_TABLE . ' (username)
            ON UPDATE CASCADE
            ON DELETE SET NULL
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