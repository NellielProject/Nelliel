<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TablePrivateMessages extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_PRIVATE_MESSAGES_TABLE;
        $this->columns_data = [
            'message_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'sender' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'recipient' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'message' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'time_sent' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'message_read' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->columns_data = [
            'message_id' => ['row_check' => false, 'auto_inc' => true, 'update' => false],
            'sender' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'recipient' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'message' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'time_sent' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'message_read' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            message_id      ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            sender          VARCHAR(50) DEFAULT NULL,
            recipient       VARCHAR(50) DEFAULT NULL,
            message         TEXT NOT NULL,
            time_sent       BIGINT NOT NULL,
            message_read    SMALLINT NOT NULL DEFAULT 0,
            moar            TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (message_id),
            CONSTRAINT fk_private_messages__users
            FOREIGN KEY (sender) REFERENCES ' . NEL_USERS_TABLE . ' (username)
            ON UPDATE CASCADE
            ON DELETE SET NULL,
            CONSTRAINT fk2_private_messages__users
            FOREIGN KEY (recipient) REFERENCES ' . NEL_USERS_TABLE . ' (username)
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