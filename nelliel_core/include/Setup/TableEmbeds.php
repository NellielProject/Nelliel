<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableEmbeds extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_EMBEDS_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'embed_name' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'data_regex' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'embed_url' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'enabled' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false],
            'notes' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'moar' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry       " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            embed_name  VARCHAR(255) NOT NULL,
            data_regex  TEXT NOT NULL,
            embed_url   TEXT NOT NULL,
            enabled     SMALLINT NOT NULL DEFAULT 0,
            notes       TEXT DEFAULT NULL,
            moar        TEXT DEFAULT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['Youtube', '/(?:http:|https:)*?\/\/(?:www\.|)(?:youtube\.com|m\.youtube\.com|youtu\.|youtube-nocookie\.com).*(?:v=|v%3D|v\/|(?:a|p)\/(?:a|u)\/\d.*\/|watch\?|vi(?:=|\/)|\/embed\/|oembed\?|be\/|e\/)([^&?%#\/\n]*)/iu', 'https://www.youtube.com/embed/$1', 1, 'From https://gist.github.com/rodrigoborgesdeoliveira/987683cfbfcc8d800192da1e73adc486']);
        $this->insertDefaultRow(['Vimeo', '/(?:http:|https:)*?\/\/(?:.*\.)?vimeo\.com\/(?:video\/)?([\d]+)(?:.+)?/iu', 'https://player.vimeo.com/video/$1', 1, '']);
        $this->insertDefaultRow(['Dailymotion', '/(?:http:|https:)*?\/\/(?:.*\.)?dailymotion\.com\/(?:embed\/)?.*?\/([0-9A-z]+)/iu', 'https://www.dailymotion.com/embed/video/$1', 1, '']);
        $this->insertDefaultRow(['Metacafe', '/(?:http:|https:)*?\/\/(?:.*\.)?metacafe\.com\/(?:(?:watch|embed)\/)?([\d]+)(\/[0-9A-z-]+)?/iu', 'https://www.metacafe.com/embed/$1$2', 0, 'Warning: Metacafe embeds autoplay for some reason and not sure how to stop that yet.']);

    }
}