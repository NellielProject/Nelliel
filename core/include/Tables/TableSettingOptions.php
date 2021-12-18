<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableSettingOptions extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_SETTING_OPTIONS_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'setting_category' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'setting_name' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'menu_data' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'setting_category' => ['row_check' => true, 'auto_inc' => false],
            'setting_name' => ['row_check' => true, 'auto_inc' => false],
            'menu_data' => ['row_check' => false, 'auto_inc' => false],
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
            setting_category    VARCHAR(50) NOT NULL,
            setting_name        VARCHAR(50) NOT NULL,
            menu_data           TEXT NOT NULL,
            moar                TEXT DEFAULT NULL,
            UNIQUE (setting_category, setting_name),
            CONSTRAINT fk_" . $this->table_name . "_" . NEL_SETTINGS_TABLE . "
            FOREIGN KEY (setting_category, setting_name) REFERENCES " . NEL_SETTINGS_TABLE . " (setting_category, setting_name)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {

    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['site', 'site_referrer_policy', '{"no-referrer": "no-referrer", "no-referrer-when-downgrade": "no-referrer-when-downgrade", "origin": "origin", "origin-when-cross-origin": "origin-when-cross-origin", "same-origin": "same-origin", "strict-origin": "strict-origin", "strict-origin-when-cross-origin": "strict-origin-when-cross-origin", "unsafe-url": "unsafe-url"}']);
        $this->insertDefaultRow(['site', 'external_link_referrer_policy', '{"no-referrer": "no-referrer", "no-referrer-when-downgrade": "no-referrer-when-downgrade", "origin": "origin", "origin-when-cross-origin": "origin-when-cross-origin", "same-origin": "same-origin", "strict-origin": "strict-origin", "strict-origin-when-cross-origin": "strict-origin-when-cross-origin", "unsafe-url": "unsafe-url"}']);
        $this->insertDefaultRow(['site', 'graphics_handler', '{"GD": "GD", "ImageMagick": "ImageMagick", "GraphicsMagick": "GraphicsMagick"}']);
        $this->insertDefaultRow(['site', 'recaptcha_type', '{"Checkbox": "CHECKBOX"}']);
        $this->insertDefaultRow(['board', 'safety_level', '{"SFW - Safe For Work": "SFW", "NSFW - Not Safe For Work": "NSFW", "NSFL - Not Safe For Life": "NSFL"}']);
        $this->insertDefaultRow(['board', 'preferred_filename', '{"Filtered original": "filtered_original", "Unix timestamp": "timestamp", "MD5": "md5", "SHA1": "sha1", "SHA256": "sha256", "SHA512": "sha2512"}']);
        $this->insertDefaultRow(['board', 'static_preview_format', '{"JPEG": "jpg", "PNG": "png", "WebP": "webp", "GIF": "gif"}']);
        $this->insertDefaultRow(['board', 'animated_preview_format', '{"GIF": "gif"}']);
        $this->insertDefaultRow(['board', 'old_threads', '{"Nothing": "NOTHING", "Prune": "PRUNE", "Archive": "ARCHIVE"}']);
    }
}