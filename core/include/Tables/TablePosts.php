<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TablePosts extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'post_number' => 'integer',
        'parent_thread' => 'integer',
        'reply_to' => 'integer',
        'name' => 'string',
        'password' => 'string',
        'tripcode' => 'string',
        'secure_tripcode' => 'string',
        'capcode' => 'string',
        'email' => 'string',
        'subject' => 'string',
        'comment' => 'string',
        'hashed_ip_address' => 'string',
        'ip_address' => 'string',
        'visitor_id' => 'string',
        'post_time' => 'integer',
        'post_time_milli' => 'integer',
        'total_uploads' => 'integer',
        'file_count' => 'integer',
        'embed_count' => 'integer',
        'op' => 'boolean',
        'sage' => 'boolean',
        'shadow' => 'boolean',
        'username' => 'string',
        'mod_comment' => 'string',
        'regen_cache' => 'boolean',
        'cache' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'post_number' => PDO::PARAM_INT,
        'parent_thread' => PDO::PARAM_INT,
        'reply_to' => PDO::PARAM_INT,
        'name' => PDO::PARAM_STR,
        'password' => PDO::PARAM_STR,
        'tripcode' => PDO::PARAM_STR,
        'secure_tripcode' => PDO::PARAM_STR,
        'capcode' => PDO::PARAM_STR,
        'email' => PDO::PARAM_STR,
        'subject' => PDO::PARAM_STR,
        'comment' => PDO::PARAM_STR,
        'hashed_ip_address' => PDO::PARAM_STR,
        'ip_address' => PDO::PARAM_LOB,
        'visitor_id' => PDO::PARAM_STR,
        'post_time' => PDO::PARAM_INT,
        'post_time_milli' => PDO::PARAM_INT,
        'total_uploads' => PDO::PARAM_INT,
        'file_count' => PDO::PARAM_INT,
        'embed_count' => PDO::PARAM_INT,
        'op' => PDO::PARAM_INT,
        'sage' => PDO::PARAM_INT,
        'shadow' => PDO::PARAM_INT,
        'username' => PDO::PARAM_STR,
        'mod_comment' => PDO::PARAM_STR,
        'regen_cache' => PDO::PARAM_INT,
        'cache' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = '_posts';
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