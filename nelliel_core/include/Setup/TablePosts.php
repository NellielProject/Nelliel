<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TablePosts extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = '_posts';
        $this->columns_data = [
            'post_number' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'parent_thread' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'reply_to' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'poster_name' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'post_password' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'tripcode' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'secure_tripcode' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'email' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'subject' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'comment' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'ip_address' => ['pdo_type' => PDO::PARAM_LOB, 'row_check' => false, 'auto_inc' => false],
            'post_time' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'post_time_milli' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'has_content' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'content_count' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'op' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'sage' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'mod_post_id' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'mod_comment' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'meta' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            post_number         " . $auto_inc[0] . " NOT NULL " . $auto_inc[1] . " PRIMARY KEY,
            parent_thread       INTEGER DEFAULT NULL,
            reply_to            INTEGER DEFAULT NULL,
            poster_name         VARCHAR(255) DEFAULT NULL,
            post_password       VARCHAR(255) DEFAULT NULL,
            tripcode            VARCHAR(255) DEFAULT NULL,
            secure_tripcode     VARCHAR(255) DEFAULT NULL,
            email               VARCHAR(255) DEFAULT NULL,
            subject             VARCHAR(255) DEFAULT NULL,
            comment             TEXT DEFAULT NULL,
            ip_address          " . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . " NOT NULL,
            post_time           BIGINT NOT NULL,
            post_time_milli     SMALLINT NOT NULL,
            has_content         SMALLINT NOT NULL DEFAULT 0,
            content_count       SMALLINT NOT NULL DEFAULT 0,
            op                  SMALLINT NOT NULL DEFAULT 0,
            sage                SMALLINT NOT NULL DEFAULT 0,
            mod_post_id         VARCHAR(255) DEFAULT NULL,
            mod_comment         TEXT DEFAULT NULL,
            meta                TEXT DEFAULT NULL,
            CONSTRAINT fk_parent_thread_" . $other_tables['threads_table'] . "_thread_id
            FOREIGN KEY (parent_thread) REFERENCES " . $other_tables['threads_table'] . "(thread_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
        ;
    }
}