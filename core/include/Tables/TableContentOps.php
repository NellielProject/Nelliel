<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableContentOps extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_CONTENT_OPS_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'content_op_label' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'content_op_url' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'images_only' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'enabled' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'notes' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'content_op_label' => ['row_check' => true, 'auto_inc' => false],
            'content_op_url' => ['row_check' => true, 'auto_inc' => false],
            'images_only' => ['row_check' => false, 'auto_inc' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false],
            'notes' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry               " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            content_op_label    VARCHAR(255) NOT NULL,
            content_op_url      TEXT NOT NULL,
            images_only         SMALLINT NOT NULL DEFAULT 0,
            enabled             SMALLINT NOT NULL DEFAULT 0,
            notes               TEXT DEFAULT NULL,
            moar                TEXT DEFAULT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['ImgOps', 'https://imgops.com/', 1, 1, 'Image Operations https://imgops.com/']);
        $this->insertDefaultRow(['EXIF', 'http://regex.info/imageinfo.cgi?url=', 1, 1, 'Jeffrey\'s Image Metadata Viewer http://regex.info']);
        $this->insertDefaultRow(['iqdb', 'http://iqdb.org/?url=', 1, 1, 'Multi-service image search https://iqdb.org']);
        $this->insertDefaultRow(['TinEye', 'https://tineye.com/search/?url=', 1, 1, 'TinEye reverse image search https://tineye.com/']);
        $this->insertDefaultRow(['Yandex', 'https://yandex.com/images/search?rpt=imageview&url=', 1, 1, 'Yandex https://yandex.com/']);
        $this->insertDefaultRow(['ASSE', 'https://trace.moe/?url=', 1, 1, 'Anime Scene Search Engine https://trace.moe/']);
    }
}