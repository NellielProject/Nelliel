<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableSettingOptions extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'setting_category' => 'string',
        'setting_name' => 'string',
        'menu_data' => 'string',
        'raw_output' => 'integer',
        'json' => 'integer',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'setting_category' => PDO::PARAM_STR,
        'setting_name' => PDO::PARAM_STR,
        'menu_data' => PDO::PARAM_STR,
        'raw_output' => PDO::PARAM_INT,
        'json' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_SETTING_OPTIONS_TABLE;
        $this->column_checks = [
            'setting_category' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'setting_name' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'menu_data' => ['row_check' => false, 'auto_inc' => false, 'update' => true],
            'raw_output' => ['row_check' => false, 'auto_inc' => false, 'update' => true],
            'json' => ['row_check' => false, 'auto_inc' => false, 'update' => true],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            setting_category    VARCHAR(50) NOT NULL,
            setting_name        VARCHAR(50) NOT NULL,
            menu_data           ' . $this->sql_compatibility->textType('LONGTEXT') . ' NOT NULL,
            raw_output          SMALLINT NOT NULL DEFAULT 0,
            json                SMALLINT NOT NULL DEFAULT 0,
            moar                ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
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
        $this->insertDefaultRow(['site', 'site_referrer_policy', '{"no-referrer": "no-referrer", "no-referrer-when-downgrade": "no-referrer-when-downgrade", "origin": "origin", "origin-when-cross-origin": "origin-when-cross-origin", "same-origin": "same-origin", "strict-origin": "strict-origin", "strict-origin-when-cross-origin": "strict-origin-when-cross-origin", "unsafe-url": "unsafe-url"}', 0, 0]);
        $this->insertDefaultRow(['site', 'external_link_referrer_policy', '{"no-referrer": "no-referrer", "no-referrer-when-downgrade": "no-referrer-when-downgrade", "origin": "origin", "origin-when-cross-origin": "origin-when-cross-origin", "same-origin": "same-origin", "strict-origin": "strict-origin", "strict-origin-when-cross-origin": "strict-origin-when-cross-origin", "unsafe-url": "unsafe-url"}', 0, 0]);
        $this->insertDefaultRow(['site', 'graphics_handler', '{"GD": "GD", "ImageMagick": "ImageMagick", "GraphicsMagick": "GraphicsMagick"}', 0, 0]);
        $this->insertDefaultRow(['board', 'safety_level', '{"SFW - Safe For Work": "SFW", "NSFW - Not Safe For Work": "NSFW", "NSFL - Not Safe For Life": "NSFL"}', 0, 0]);
        $this->insertDefaultRow(['board', 'preferred_filename', '{"Filtered original": "filtered_original", "Unix timestamp": "timestamp", "MD5": "md5", "SHA1": "sha1", "SHA256": "sha256", "SHA512": "sha512"}', 0, 0]);
        $this->insertDefaultRow(['board', 'static_preview_format', '{"JPEG": "jpg", "PNG": "png", "WebP": "webp", "GIF": "gif"}', 0, 0]);
        $this->insertDefaultRow(['board', 'animated_preview_format', '{"GIF": "gif"}', 0, 0]);
        $this->insertDefaultRow(['board', 'old_threads', '{"Nothing": "NOTHING", "Prune": "PRUNE", "Archive": "ARCHIVE"}', 0, 0]);
        $this->insertDefaultRow(['site', 'name', '', 1, 0]);
        $this->insertDefaultRow(['site', 'description', '', 1, 0]);
        $this->insertDefaultRow(['site', 'global_announcement', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_content_disclaimer', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_footer_text', '', 1, 0]);
        $this->insertDefaultRow(['site', 'error_message_header', '', 1, 0]);
        $this->insertDefaultRow(['board', 'name', '', 1, 0]);
        $this->insertDefaultRow(['board', 'description', '', 1, 0]);
        $this->insertDefaultRow(['board', 'board_content_disclaimer', '', 1, 0]);
        $this->insertDefaultRow(['board', 'board_footer_text', '', 1, 0]);
        $this->insertDefaultRow(['board', 'shadow_message_moved', '', 1, 0]);
        $this->insertDefaultRow(['board', 'shadow_message_merged', '', 1, 0]);
        $this->insertDefaultRow(['board', 'ban_page_extra_text', '', 1, 0]);
        $this->insertDefaultRow(['board', 'post_backlinks_label', '', 1, 0]);
        $this->insertDefaultRow(['board', 'name_field_label', '', 1, 0]);
        $this->insertDefaultRow(['board', 'email_field_label', '', 1, 0]);
        $this->insertDefaultRow(['board', 'subject_field_label', '', 1, 0]);
        $this->insertDefaultRow(['board', 'comment_field_label', '', 1, 0]);
        $this->insertDefaultRow(['board', 'fgsfds_field_label', '', 1, 0]);
        $this->insertDefaultRow(['board', 'password_field_label', '', 1, 0]);
        $this->insertDefaultRow(['board', 'files_form_label', '', 1, 0]);
        $this->insertDefaultRow(['board', 'embeds_form_label', '', 1, 0]);
        $this->insertDefaultRow(['board', 'flags_form_label', '', 1, 0]);
        $this->insertDefaultRow(['board', 'captcha_form_label', '', 1, 0]);
        $this->insertDefaultRow(['board', 'thread_mod_options_link_set', '', 0, 1]);
        $this->insertDefaultRow(['board', 'post_mod_options_link_set', '', 0, 1]);
        $this->insertDefaultRow(['board', 'upload_mod_options_link_set', '', 0, 1]);
        $this->insertDefaultRow(['board', 'mod_links_left_bracket', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_right_bracket', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_ban', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_delete', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_delete_by_ip', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_global_delete_by_ip', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_ban_and_delete', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_lock', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_unlock', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_sticky', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_unsticky', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_permasage', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_unpermasage', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_cyclic', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_non_cyclic', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_edit', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_move', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_merge', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_spoiler', '', 1, 0]);
        $this->insertDefaultRow(['board', 'mod_links_unspoiler', '', 1, 0]);
        $this->insertDefaultRow(['board', 'thread_options_link_set', '', 0, 1]);
        $this->insertDefaultRow(['board', 'post_options_link_set', '', 0, 1]);
        $this->insertDefaultRow(['board', 'upload_options_link_set', '', 0, 1]);
        $this->insertDefaultRow(['board', 'content_links_left_bracket', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_right_bracket', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_reply', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_expand_thread', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_collapse_thread', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_show_thread', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_hide_thread', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_show_post', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_hide_post', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_show_file', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_hide_file', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_show_embed', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_hide_embed', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_show_upload_meta', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_hide_upload_meta', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_cite_post', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_first_posts', '', 1, 0]);
        $this->insertDefaultRow(['board', 'content_links_last_posts', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_navigation_link_set', '', 0, 1]);
        $this->insertDefaultRow(['site', 'logged_in_link_set', '', 0, 1]);
        $this->insertDefaultRow(['site', 'site_links_left_bracket', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_links_right_bracket', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_links_home', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_links_news', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_links_faq', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_links_about_nelliel', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_links_blank_page', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_links_account', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_links_site_panel', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_links_global_panel', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_links_board_panel', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_links_board_list', '', 1, 0]);
        $this->insertDefaultRow(['site', 'site_links_logout', '', 1, 0]);
        $this->insertDefaultRow(['board', 'filesize_unit_prefix', '{"Bytes (B)": "B", "Kilobytes (KB)": "KB", "Kibibytes (KiB)": "KiB", "Megabytes (MB)": "MB", "Mebibytes (MiB)": "MiB", "Gigabytes (GB)": "GB", "Gibibytes (GiB)": "GiB", "Terabytes (TB)": "TB", "Tebibytes (TiB)": "TiB", "Petabytes (PB)": "PB", "Pebibytes (PiB)": "PiB", "Exabytes (EB)": "EB", "Exbibytes (EiB)": "EiB", "Zettabytes (ZB)": "ZB", "Zebibytes (ZiB)": "ZiB", "Yottabytes (YB)": "YB", "Yobibytes (YiB)": "YiB"}', 0, 0]);
        $this->insertDefaultRow(['site', 'dnsbl_exceptions', '', 0, 1]);
        $this->insertDefaultRow(['board', 'anonymous_names', '', 1, 1]);
        $this->insertDefaultRow(['board', 'automatic_gets', '', 0, 1]);
        $this->insertDefaultRow(['board', 'first_posts_increments', '', 0, 1]);
        $this->insertDefaultRow(['board', 'last_posts_increments', '', 0, 1]);
    }
}