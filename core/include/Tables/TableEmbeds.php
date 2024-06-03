<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableEmbeds extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'embed_id' => 'string',
        'label' => 'string',
        'regex' => 'string',
        'url' => 'string',
        'enabled' => 'boolean',
        'notes' => 'string',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'embed_id' => PDO::PARAM_STR,
        'label' => PDO::PARAM_STR,
        'regex' => PDO::PARAM_STR,
        'url' => PDO::PARAM_STR,
        'enabled' => PDO::PARAM_INT,
        'notes' => PDO::PARAM_STR,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_EMBEDS_TABLE;
        $this->column_checks = [
            'embed_id' => ['row_check' => true, 'auto_inc' => true, 'update' => false],
            'label' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'regex' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'url' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
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
            embed_id    ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            label       VARCHAR(255) NOT NULL,
            regex       TEXT NOT NULL,
            url         TEXT NOT NULL,
            enabled     SMALLINT NOT NULL DEFAULT 0,
            notes       TEXT DEFAULT NULL,
            moar        ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (embed_id)
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['Youtube', '/(?:http:|https:)*?\/\/(?:www\.|)(?:youtube\.com|m\.youtube\.com|youtu\.|youtube-nocookie\.com).*(?:v=|v%3D|v\/|(?:a|p)\/(?:a|u)\/\d.*\/|watch\?|vi(?:=|\/)|\/embed\/|oembed\?|be\/|e\/)([a-zA-Z0-9_-]*)/iu', 'https://www.youtube.com/embed/$1', 1, 'From https://gist.github.com/rodrigoborgesdeoliveira/987683cfbfcc8d800192da1e73adc486']);
        $this->insertDefaultRow(['Vimeo', '/(?:http:|https:)*?\/\/(?:.*\.)?vimeo\.com\/(?:video\/)?([\d]+)(?:.+)?/iu', 'https://player.vimeo.com/video/$1', 1, null]);
        $this->insertDefaultRow(['Dailymotion', '/(?:http:|https:)*?\/\/(?:.*\.)?dailymotion\.com\/(?:embed\/)?.*?\/([0-9A-z]+)/iu', 'https://www.dailymotion.com/embed/video/$1', 1, null]);
    }
}