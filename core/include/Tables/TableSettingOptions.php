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
            'setting_category' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'setting_name' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'menu_data' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'raw_output' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'setting_category' => ['row_check' => true, 'auto_inc' => false],
            'setting_name' => ['row_check' => true, 'auto_inc' => false],
            'menu_data' => ['row_check' => false, 'auto_inc' => false],
            'raw_output' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            setting_category    VARCHAR(50) NOT NULL,
            setting_name        VARCHAR(50) NOT NULL,
            menu_data           TEXT NOT NULL,
            raw_output          SMALLINT NOT NULL DEFAULT 0,
            moar                TEXT DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (setting_category, setting_name),
            UNIQUE (setting_category, setting_name),
            CONSTRAINT fk_setting_options__settings
            FOREIGN KEY (setting_category, setting_name) REFERENCES ' . NEL_SETTINGS_TABLE . ' (setting_category, setting_name)
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
        $this->insertDefaultRow(['site', 'site_referrer_policy', '{"no-referrer": "no-referrer", "no-referrer-when-downgrade": "no-referrer-when-downgrade", "origin": "origin", "origin-when-cross-origin": "origin-when-cross-origin", "same-origin": "same-origin", "strict-origin": "strict-origin", "strict-origin-when-cross-origin": "strict-origin-when-cross-origin", "unsafe-url": "unsafe-url"}', 0]);
        $this->insertDefaultRow(['site', 'external_link_referrer_policy', '{"no-referrer": "no-referrer", "no-referrer-when-downgrade": "no-referrer-when-downgrade", "origin": "origin", "origin-when-cross-origin": "origin-when-cross-origin", "same-origin": "same-origin", "strict-origin": "strict-origin", "strict-origin-when-cross-origin": "strict-origin-when-cross-origin", "unsafe-url": "unsafe-url"}', 0]);
        $this->insertDefaultRow(['site', 'graphics_handler', '{"GD": "GD", "ImageMagick": "ImageMagick", "GraphicsMagick": "GraphicsMagick"}', 0]);
        $this->insertDefaultRow(['site', 'recaptcha_type', '{"Checkbox": "CHECKBOX"}', 0]);
        $this->insertDefaultRow(['board', 'safety_level', '{"SFW - Safe For Work": "SFW", "NSFW - Not Safe For Work": "NSFW", "NSFL - Not Safe For Life": "NSFL"}', 0]);
        $this->insertDefaultRow(['board', 'preferred_filename', '{"Filtered original": "filtered_original", "Unix timestamp": "timestamp", "MD5": "md5", "SHA1": "sha1", "SHA256": "sha256", "SHA512": "sha2512"}', 0]);
        $this->insertDefaultRow(['board', 'static_preview_format', '{"JPEG": "jpg", "PNG": "png", "WebP": "webp", "GIF": "gif"}', 0]);
        $this->insertDefaultRow(['board', 'animated_preview_format', '{"GIF": "gif"}', 0]);
        $this->insertDefaultRow(['board', 'old_threads', '{"Nothing": "NOTHING", "Prune": "PRUNE", "Archive": "ARCHIVE"}', 0]);
        $this->insertDefaultRow(['site', 'description', '', 1]);
        $this->insertDefaultRow(['site', 'site_content_disclaimer', '', 1]);
        $this->insertDefaultRow(['site', 'site_footer_text', '', 1]);
        $this->insertDefaultRow(['board', 'description', '', 1]);
        $this->insertDefaultRow(['board', 'board_content_disclaimer', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_delimiter_left', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_delimiter_right', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_ban', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_delete', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_delete_by_ip', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_global_delete_by_ip', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_ban_and_delete', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_lock', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_unlock', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_sticky', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_unsticky', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_permasage', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_unpermasage', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_cyclic', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_non_cyclic', '', 1]);
        $this->insertDefaultRow(['board', 'mod_links_edit_post', '', 1]);
    }
}