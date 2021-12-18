<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableEmbeds extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_EMBEDS_TABLE;
        $this->column_types = [
            'embed_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'label' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'regex' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'url' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'enabled' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'notes' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'embed_id' => ['row_check' => true, 'auto_inc' => false],
            'label' => ['row_check' => false, 'auto_inc' => false],
            'regex' => ['row_check' => false, 'auto_inc' => false],
            'url' => ['row_check' => false, 'auto_inc' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false],
            'notes' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            embed_id    VARCHAR(50) PRIMARY KEY NOT NULL,
            label       VARCHAR(255) NOT NULL,
            regex       TEXT NOT NULL,
            url         TEXT NOT NULL,
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
        $this->insertDefaultRow(['youtube', 'Youtube', '/(?:http:|https:)*?\/\/(?:www\.|)(?:youtube\.com|m\.youtube\.com|youtu\.|youtube-nocookie\.com).*(?:v=|v%3D|v\/|(?:a|p)\/(?:a|u)\/\d.*\/|watch\?|vi(?:=|\/)|\/embed\/|oembed\?|be\/|e\/)([^&?%#\/\n]*)/iu', 'https://www.youtube.com/embed/$1', 1, 'From https://gist.github.com/rodrigoborgesdeoliveira/987683cfbfcc8d800192da1e73adc486']);
        $this->insertDefaultRow(['vimeo', 'Vimeo', '/(?:http:|https:)*?\/\/(?:.*\.)?vimeo\.com\/(?:video\/)?([\d]+)(?:.+)?/iu', 'https://player.vimeo.com/video/$1', 1, null]);
        $this->insertDefaultRow(['dailymotion', 'Dailymotion', '/(?:http:|https:)*?\/\/(?:.*\.)?dailymotion\.com\/(?:embed\/)?.*?\/([0-9A-z]+)/iu', 'https://www.dailymotion.com/embed/video/$1', 1, null]);
    }
}