<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TablePosts extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = '_posts';
        $this->column_types = [
            'post_number' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'parent_thread' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'reply_to' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'name' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'password' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'tripcode' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'secure_tripcode' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'capcode' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'email' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'subject' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'comment' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'hashed_ip_address' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'ip_address' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_LOB],
            'visitor_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'post_time' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'post_time_milli' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'total_uploads' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'file_count' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'embed_count' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'op' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'sage' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'shadow' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'username' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'mod_comment' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'regen_cache' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'cache' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'post_number' => ['row_check' => true, 'auto_inc' => true, 'update' => false],
            'parent_thread' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'reply_to' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'name' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'password' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'tripcode' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'secure_tripcode' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'capcode' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'email' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'subject' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'comment' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'hashed_ip_address' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'ip_address' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'visitor_id' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'post_time' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'post_time_milli' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'total_uploads' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'file_count' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'embed_count' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'op' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'sage' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'shadow' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'username' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'mod_comment' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'regen_cache' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'cache' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            post_number         ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            parent_thread       INTEGER DEFAULT NULL,
            reply_to            INTEGER DEFAULT NULL,
            name                VARCHAR(255) DEFAULT NULL,
            password            VARCHAR(255) DEFAULT NULL,
            tripcode            VARCHAR(255) DEFAULT NULL,
            secure_tripcode     VARCHAR(255) DEFAULT NULL,
            capcode             VARCHAR(255) DEFAULT NULL,
            email               VARCHAR(255) DEFAULT NULL,
            subject             VARCHAR(255) DEFAULT NULL,
            comment             ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            hashed_ip_address   VARCHAR(128) DEFAULT NULL,
            ip_address          ' . $this->sql_compatibility->sqlAlternatives('VARBINARY', '16') . ' DEFAULT NULL,
            visitor_id          VARCHAR(128) DEFAULT NULL,
            post_time           BIGINT NOT NULL,
            post_time_milli     SMALLINT NOT NULL,
            total_uploads       SMALLINT NOT NULL DEFAULT 0,
            file_count          SMALLINT NOT NULL DEFAULT 0,
            embed_count         SMALLINT NOT NULL DEFAULT 0,
            op                  SMALLINT NOT NULL DEFAULT 0,
            sage                SMALLINT NOT NULL DEFAULT 0,
            shadow              SMALLINT NOT NULL DEFAULT 0,
            username            VARCHAR(50) DEFAULT NULL,
            mod_comment         TEXT DEFAULT NULL,
            regen_cache         SMALLINT NOT NULL DEFAULT 0,
            cache               ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            moar                ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (post_number),
            CONSTRAINT fk_' . $this->table_name . '__threads
            FOREIGN KEY (parent_thread) REFERENCES ' . $other_tables['threads_table'] . ' (thread_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk_' . $this->table_name . '__users
            FOREIGN KEY (username) REFERENCES ' . NEL_USERS_TABLE . ' (username)
            ON UPDATE CASCADE
            ON DELETE SET NULL,
            CONSTRAINT fk_' . $this->table_name . '__ip_info
            FOREIGN KEY (hashed_ip_address) REFERENCES ' . NEL_IP_INFO_TABLE . ' (hashed_ip_address)
            ON UPDATE CASCADE
            ON DELETE SET NULL,
            CONSTRAINT fk_' . $this->table_name . '__visitor_info
            FOREIGN KEY (visitor_id) REFERENCES ' . NEL_VISITOR_INFO_TABLE . ' (visitor_id)
            ON UPDATE CASCADE
            ON DELETE SET NULL
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
        $this->database->query('CREATE INDEX ix_' . $this->table_name . '__parent_thread ON ' . $this->table_name . ' (parent_thread)');
        $this->database->query('CREATE INDEX ix_' . $this->table_name . '__hashed_ip_address ON ' . $this->table_name . ' (hashed_ip_address)');
    }

    public function insertDefaults()
    {
    }
}