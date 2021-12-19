<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableUploads extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = '_uploads';
        $this->increment_column = 'upload_id';
        $this->column_types = [
            'upload_id' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'parent_thread' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'post_ref' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'upload_order' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'category' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'format' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'mime' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'filename' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'extension' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'original_filename' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'display_width' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'display_height' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'static_preview_name' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'animated_preview_name' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'preview_width' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'preview_height' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'filesize' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'md5' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'sha1' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'sha256' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'sha512' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'embed_url' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'spoiler' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'deleted' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'exif' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'regen_cache' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'cache' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'upload_id' => ['row_check' => false, 'auto_inc' => true],
            'parent_thread' => ['row_check' => false, 'auto_inc' => false],
            'post_ref' => ['row_check' => true, 'auto_inc' => false],
            'upload_order' => ['row_check' => true, 'auto_inc' => false],
            'category' => ['row_check' => false, 'auto_inc' => false],
            'format' => ['row_check' => false, 'auto_inc' => false],
            'mime' => ['row_check' => false, 'auto_inc' => false],
            'filename' => ['row_check' => false, 'auto_inc' => false],
            'original_filename' => ['row_check' => false, 'auto_inc' => false],
            'extension' => ['row_check' => false, 'auto_inc' => false],
            'extension' => ['row_check' => false, 'auto_inc' => false],
            'display_width' => ['row_check' => false, 'auto_inc' => false],
            'display_height' => ['row_check' => false, 'auto_inc' => false],
            'static_preview_name' => ['row_check' => false, 'auto_inc' => false],
            'animated_preview_name' => ['row_check' => false, 'auto_inc' => false],
            'preview_width' => ['row_check' => false, 'auto_inc' => false],
            'preview_height' => ['row_check' => false, 'auto_inc' => false],
            'filesize' => ['row_check' => false, 'auto_inc' => false],
            'md5' => ['row_check' => false, 'auto_inc' => false],
            'sha1' => ['row_check' => false, 'auto_inc' => false],
            'sha256' => ['row_check' => false, 'auto_inc' => false],
            'sha512' => ['row_check' => false, 'auto_inc' => false],
            'embed_url' => ['row_check' => false, 'auto_inc' => false],
            'spoiler' => ['row_check' => false, 'auto_inc' => false],
            'deleted' => ['row_check' => false, 'auto_inc' => false],
            'exif' => ['row_check' => false, 'auto_inc' => false],
            'regen_cache' => ['row_check' => false, 'auto_inc' => false],
            'cache' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
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
            exif                    TEXT DEFAULT NULL,
            regen_cache             SMALLINT NOT NULL DEFAULT 0,
            cache                   TEXT DEFAULT NULL,
            moar                    TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (upload_id),
            CONSTRAINT fk_' . $other_tables['db_prefix'] . '_uploads__threads
            FOREIGN KEY (parent_thread) REFERENCES ' . $other_tables['threads_table'] . ' (thread_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE,
            CONSTRAINT fk_' . $other_tables['db_prefix'] . '_uploads__posts
            FOREIGN KEY (post_ref) REFERENCES ' . $other_tables['posts_table'] . ' (post_number)
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