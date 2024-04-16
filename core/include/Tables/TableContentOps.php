<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableContentOps extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'op_id' => 'integer',
        'label' => 'string',
        'url' => 'string',
        'images_only' => 'boolean',
        'enabled' => 'boolean',
        'notes' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'op_id' => PDO::PARAM_INT,
        'label' => PDO::PARAM_STR,
        'url' => PDO::PARAM_STR,
        'images_only' => PDO::PARAM_INT,
        'enabled' => PDO::PARAM_INT,
        'notes' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_CONTENT_OPS_TABLE;
        $this->column_checks = [
            'op_id' => ['row_check' => true, 'auto_inc' => true, 'update' => false],
            'label' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'url' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'images_only' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'notes' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            op_id           ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            label           VARCHAR(255) NOT NULL,
            url             TEXT NOT NULL,
            images_only     SMALLINT NOT NULL DEFAULT 0,
            enabled         SMALLINT NOT NULL DEFAULT 0,
            notes           TEXT DEFAULT NULL,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (op_id)
        ) ' . $options . ';';

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