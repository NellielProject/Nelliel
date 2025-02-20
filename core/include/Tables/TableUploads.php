<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableUploads extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'upload_id' => 'integer',
        'parent_thread' => 'integer',
        'post_ref' => 'integer',
        'upload_order' => 'integer',
        'category' => 'string',
        'format' => 'string',
        'mime' => 'string',
        'filename' => 'string',
        'extension' => 'string',
        'original_filename' => 'string',
        'display_width' => 'integer',
        'display_height' => 'integer',
        'static_preview_name' => 'string',
        'animated_preview_name' => 'string',
        'preview_width' => 'integer',
        'preview_height' => 'integer',
        'filesize' => 'integer',
        'md5' => 'string',
        'sha1' => 'string',
        'sha256' => 'string',
        'sha512' => 'string',
        'embed_url' => 'string',
        'spoiler' => 'boolean',
        'deleted' => 'boolean',
        'shadow' => 'boolean',
        'exif' => 'string',
        'regen_cache' => 'boolean',
        'cache' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'upload_id' => PDO::PARAM_INT,
        'parent_thread' => PDO::PARAM_INT,
        'post_ref' => PDO::PARAM_INT,
        'upload_order' => PDO::PARAM_INT,
        'category' => PDO::PARAM_STR,
        'format' => PDO::PARAM_STR,
        'mime' => PDO::PARAM_STR,
        'filename' => PDO::PARAM_STR,
        'extension' => PDO::PARAM_STR,
        'original_filename' => PDO::PARAM_STR,
        'display_width' => PDO::PARAM_INT,
        'display_height' => PDO::PARAM_INT,
        'static_preview_name' => PDO::PARAM_STR,
        'animated_preview_name' => PDO::PARAM_STR,
        'preview_width' => PDO::PARAM_INT,
        'preview_height' => PDO::PARAM_INT,
        'filesize' => PDO::PARAM_INT,
        'md5' => PDO::PARAM_STR,
        'sha1' => PDO::PARAM_STR,
        'sha256' => PDO::PARAM_STR,
        'sha512' => PDO::PARAM_STR,
        'embed_url' => PDO::PARAM_STR,
        'spoiler' => PDO::PARAM_INT,
        'deleted' => PDO::PARAM_INT,
        'shadow' => PDO::PARAM_INT,
        'exif' => PDO::PARAM_STR,
        'regen_cache' => PDO::PARAM_INT,
        'cache' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = '_uploads';
        $this->increment_column = 'upload_id';
        $this->column_checks = [
            'upload_id' => ['row_check' => true, 'auto_inc' => true, 'update' => false],
            'parent_thread' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'post_ref' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'upload_order' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'category' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'format' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'mime' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'filename' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'original_filename' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'extension' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'extension' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'display_width' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'display_height' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'static_preview_name' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'animated_preview_name' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'preview_width' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'preview_height' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'filesize' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'md5' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'sha1' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'sha256' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'sha512' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'embed_url' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'spoiler' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'deleted' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'shadow' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'exif' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
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
            upload_id               ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            parent_thread           INTEGER DEFAULT NULL,
            post_ref                INTEGER DEFAULT NULL,
            upload_order            SMALLINT NOT NULL DEFAULT 1,
            category                VARCHAR(50) NOT NULL,
            format                  VARCHAR(50) NOT NULL,
            mime                    VARCHAR(255) DEFAULT NULL,
            filename                VARCHAR(255) DEFAULT NULL,
            extension               VARCHAR(20) DEFAULT NULL,
            original_filename       VARCHAR(255) DEFAULT NULL,
            display_width           INTEGER DEFAULT NULL,
            display_height          INTEGER DEFAULT NULL,
            static_preview_name     VARCHAR(255) DEFAULT NULL,
            animated_preview_name   VARCHAR(255) DEFAULT NULL,
            preview_width           SMALLINT DEFAULT NULL,
            preview_height          SMALLINT DEFAULT NULL,
            filesize                BIGINT DEFAULT NULL,
            md5                     CHAR(32) DEFAULT NULL,
            sha1                    CHAR(40) DEFAULT NULL,
            sha256                  CHAR(64) DEFAULT NULL,
            sha512                  CHAR(128) DEFAULT NULL,
            embed_url               VARCHAR(2000) DEFAULT NULL,
            spoiler                 SMALLINT NOT NULL DEFAULT 0,
            deleted                 SMALLINT NOT NULL DEFAULT 0,
            shadow                  SMALLINT NOT NULL DEFAULT 0,
            exif                    TEXT DEFAULT NULL,
            regen_cache             SMALLINT NOT NULL DEFAULT 0,
            cache                   TEXT DEFAULT NULL,
            moar                    ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (upload_id),
            CONSTRAINT fk_' . $this->table_name . '__threads
            FOREIGN KEY (parent_thread) REFERENCES ' . $other_tables['threads_table'] . ' (thread_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk_' . $this->table_name . '__posts
            FOREIGN KEY (post_ref) REFERENCES ' . $other_tables['posts_table'] . ' (post_number)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
        $this->database->query('CREATE INDEX ix_' . $this->table_name . '__post_ref ON ' . $this->table_name . ' (post_ref)');
        $this->database->query('CREATE INDEX ix_' . $this->table_name . '__filename ON ' . $this->table_name . ' (filename)');
        $this->database->query('CREATE INDEX ix_' . $this->table_name . '__static_preview_name ON ' . $this->table_name . ' (static_preview_name)');
        $this->database->query('CREATE INDEX ix_' . $this->table_name . '__animated_preview_name ON ' . $this->table_name . ' (animated_preview_name)');
        $this->database->query('CREATE INDEX ix_' . $this->table_name . '__md5 ON ' . $this->table_name . ' (md5)');
    }

    public function insertDefaults()
    {
    }
}