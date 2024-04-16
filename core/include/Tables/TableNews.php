<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableNews extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'article_id' => 'integer',
        'username' => 'string',
        'name' => 'string',
        'time' => 'integer',
        'headline' => 'string',
        'text' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'article_id' => PDO::PARAM_INT,
        'username' => PDO::PARAM_STR,
        'name' => PDO::PARAM_STR,
        'time' => PDO::PARAM_INT,
        'headline' => PDO::PARAM_STR,
        'text' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_NEWS_TABLE;
        $this->column_checks = [
            'article_id' => ['row_check' => false, 'auto_inc' => true, 'update' => false],
            'username' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'name' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'time' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'headline' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'text' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            article_id  ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            username    VARCHAR(50) DEFAULT NULL,
            name        VARCHAR(255) NOT NULL,
            time        BIGINT NOT NULL,
            headline    VARCHAR(255) NOT NULL,
            text        ' . $this->sql_compatibility->textType('LONGTEXT') . ' NOT NULL,
            moar        ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (article_id),
            CONSTRAINT fk_news__users
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