<?php
declare(strict_types = 1);

namespace Nelliel\Setup;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Tables\TableBoardDefaults;
use Nelliel\Tables\TableSettings;
use Nelliel\Utility\FileHandler;
use PDO;
use Nelliel\Tables\TableSettingOptions;

class BetaMigrations
{
    private $file_handler;
    private $upgrade;

    function __construct(FileHandler $file_handler, Upgrade $upgrade)
    {
        $this->file_handler = $file_handler;
        $this->upgrade = $upgrade;
    }

    public function doMigrations(): int
    {
        $migration_count = 0;

        switch ($this->upgrade->installedVersion()) {
            case 'v0.9.25':
                $target_version = NELLIEL_VERSION;
                echo sprintf(__('Updating from v0.9.25 to %s...'), $target_version) . '<br>';
                $core_sqltype = nel_database('core')->config()['sqltype'];

                // Update setting options table
                nel_database('core')->exec(
                    'ALTER TABLE "nelliel_menu_data" RENAME TO ' . NEL_SETTING_OPTIONS_TABLE . '');
                nel_database('core')->exec(
                    'ALTER TABLE "' . NEL_SETTING_OPTIONS_TABLE . '" ADD COLUMN raw_output SMALLINT NOT NULL DEFAULT 0');
                $setting_options_table = new TableSettingOptions(nel_database('core'),
                    nel_utilities()->sqlCompatibility());
                $setting_options_table->insertDefaults();

                echo ' - ' . __('Setting options table updated.') . '<br>';

                // Update filetypes table
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . NEL_FILETYPES_TABLE . '" CHANGE COLUMN mime mimetypes TEXT NOT NULL');
                } else {
                    nel_database('core')->exec('ALTER TABLE "' . NEL_FILETYPES_TABLE . '" RENAME mime TO mimetypes');
                    nel_database('core')->exec(
                        'ALTER TABLE "' . NEL_FILETYPES_TABLE . '" ALTER COLUMN mimetypes TYPE TEXT');
                    nel_database('core')->exec(
                        'ALTER TABLE "' . NEL_FILETYPES_TABLE . '" ALTER COLUMN mimetypes SET NOT NULL');
                }

                $old_data = nel_database('core')->executeFetchAll(
                    'SELECT "format", "mimetypes" FROM "' . NEL_FILETYPES_TABLE . '"', PDO::FETCH_ASSOC);

                $multiples = ['bmp' => '["image/bmp", "image/x-bmp"]', 'tgs' => '["image/targa", "image/x-tga"]',
                    'pict' => '["image/pict", "image/x-pict"]', 'aiff' => '["audio/aiff", "audio/x-aiff"]',
                    'm4a' => '["audio/mp4", "audio/x-m4a"]', 'flac' => '["audio/flac", "audio/x-flac"]',
                    'midi' => '["audio/midi", "audio/x-midi"]', 'rtf' => '["text/rtf", "application/rtf"]',
                    'doc' => '["application/vnd.ms-word", "application/msword"]',
                    'gzip' => '["application/gzip", "application/x-gzip"]',
                    'rar' => '["application/vnd.rar", "application/x-rar-compressed"]',
                    'stuffit' => '["application/x-stuffit", "application/x-sit"]',
                    'swf' => '["application/vnd.adobe.flash-movie", "application/x-shockwave-flash"]'];

                foreach ($old_data as $data) {
                    $new_value = '["' . $data['mimetypes'] . '"]';

                    if (array_key_exists($data['format'], $multiples)) {
                        $new_value = $multiples[$data['format']];
                    }

                    $prepared = nel_database('core')->prepare(
                        'UPDATE "' . NEL_FILETYPES_TABLE . '" SET "mimetypes" = :mimetypes WHERE "format" = :format');
                    $prepared->bindValue(':mimetypes', $new_value, PDO::PARAM_STR);
                    $prepared->bindValue(':format', $data['format'], PDO::PARAM_STR);
                    nel_database('core')->executePrepared($prepared, null);
                }

                nel_database('core')->exec(
                    'UPDATE "' . NEL_FILETYPES_TABLE .
                    '" SET "extensions" = \'["3gp", "3gpp"]\' WHERE "format" = \'3gp\'');
                echo ' - ' . __('Filetypes table updated.') . '<br>';

                // Update users table
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . NEL_USERS_TABLE .
                        '" CHANGE COLUMN user_password password VARCHAR(255) NOT NULL');
                } else {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . NEL_USERS_TABLE . '" RENAME COLUMN user_password TO password');
                }

                $usernames = nel_database('core')->executeFetchAll('SELECT "username" FROM "' . NEL_USERS_TABLE . '"',
                    PDO::FETCH_COLUMN);
                $prepared = nel_database('core')->prepare(
                    'UPDATE "' . NEL_USERS_TABLE . '" SET "username" = :username_lower WHERE "username" = :username');

                foreach ($usernames as $username) {
                    $username_lower = utf8_strtolower($username);
                    $prepared->bindValue(':username_lower', $username_lower, PDO::PARAM_STR);
                    $prepared->bindValue(':username', $username, PDO::PARAM_STR);
                    nel_database('core')->executePrepared($prepared, null);
                }

                echo ' - ' . __('Users table updated.') . '<br>';

                // Update archive table
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    $prefixes = nel_database('core')->executeFetchAll(
                        'SELECT "db_prefix" FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);

                    foreach ($prefixes as $prefix) {
                        nel_database('core')->exec(
                            'ALTER TABLE "' . $prefix . '_archives' . '" MODIFY COLUMN thread_data LONGTEXT NOT NULL');
                    }

                    echo ' - ' . __('Archive tables updated.') . '<br>';
                }

                // Update settings and config tables
                $board_setting_names = nel_database('core')->executeFetchAll(
                    'SELECT "setting_name" FROM "' . NEL_SETTINGS_TABLE . '" WHERE "setting_category" = \'board\'',
                    PDO::FETCH_COLUMN);
                $ui_removals = ['ui_delimiter_left', 'ui_delimiter_right', 'ui_hide_thread', 'ui_show_thread',
                    'ui_hide_post', 'ui_show_post', 'ui_hide_file', 'ui_show_file', 'ui_hide_embed', 'ui_show_embed',
                    'ui_cite_post', 'ui_reply_to_thread', 'ui_more_file_info', 'ui_less_file_info', 'ui_expand_thread',
                    'ui_collapse_thread'];
                $mod_links_name_updates = ['ui_mod_ban', 'ui_mod_delete', 'ui_mod_delete_by_ip',
                    'ui_mod_global_delete_by_ip', 'ui_mod_ban_and_delete', 'ui_mod_lock', 'ui_mod_unlock',
                    'ui_mod_sticky', 'ui_mod_unsticky', 'ui_mod_permasage', 'ui_mod_unpermasage', 'ui_mod_cyclic',
                    'ui_mod_non_cyclic', 'ui_mod_edit_post'];
                $settings_update = nel_database('core')->prepare(
                    'UPDATE "' . NEL_SETTINGS_TABLE .
                    '" SET "setting_name" = :new_name WHERE "setting_name" = :old_name AND "setting_category" = \'board\'');
                $board_defaults_update = nel_database('core')->prepare(
                    'UPDATE "' . NEL_BOARD_DEFAULTS_TABLE .
                    '" SET "setting_name" = :new_name WHERE "setting_name" = :old_name');
                $board_configs_update = nel_database('core')->prepare(
                    'UPDATE "' . NEL_BOARD_CONFIGS_TABLE .
                    '" SET "setting_name" = :new_name WHERE "setting_name" = :old_name');
                $settings_delete = nel_database('core')->prepare(
                    'DELETE FROM "' . NEL_SETTINGS_TABLE .
                    '" WHERE "setting_name" = :name AND "setting_category" = \'board\'');
                $board_defaults_delete = nel_database('core')->prepare(
                    'DELETE FROM "' . NEL_BOARD_DEFAULTS_TABLE . '" WHERE "setting_name" = :name');
                $board_configs_delete = nel_database('core')->prepare(
                    'DELETE FROM "' . NEL_BOARD_CONFIGS_TABLE . '" WHERE "setting_name" = :name');

                foreach ($board_setting_names as $name) {
                    if (in_array($name, $ui_removals)) {
                        $settings_delete->bindValue(':name', $name);
                        nel_database('core')->executePrepared($settings_delete);

                        $board_defaults_delete->bindValue(':name', $name);
                        nel_database('core')->executePrepared($board_defaults_delete);

                        $board_configs_delete->bindValue(':name', $name);
                        nel_database('core')->executePrepared($board_configs_delete);
                    }

                    if (in_array($name, $mod_links_name_updates)) {
                        $new_name = str_replace('ui_mod_', 'mod_links_', $name);
                        $settings_update->bindValue(':new_name', $new_name);
                        $settings_update->bindValue(':old_name', $name);
                        nel_database('core')->executePrepared($settings_update);

                        $board_defaults_update->bindValue(':new_name', $new_name);
                        $board_defaults_update->bindValue(':old_name', $name);
                        nel_database('core')->executePrepared($board_defaults_update);

                        $board_configs_update->bindValue(':new_name', $new_name);
                        $board_configs_update->bindValue(':old_name', $name);
                        nel_database('core')->executePrepared($board_configs_update);
                    }
                }

                $new_site_settings = [];
                $new_board_settings = ['mod_links_delimiter_left', 'mod_links_delimiter_right', 'enable_index',
                    'enable_catalog'];
                $settings_table = new TableSettings(nel_database('core'), nel_utilities()->sqlCompatibility());
                $settings_table->insertDefaults();
                $setting_options_table = new TableSettingOptions(nel_database('core'),
                    nel_utilities()->sqlCompatibility());
                $setting_options_table->insertDefaults();
                $board_defaults_table = new TableBoardDefaults(nel_database('core'), nel_utilities()->sqlCompatibility());
                $board_defaults_table->insertDefaults();
                $this->copyToSiteConfig($new_site_settings);
                $board_ids = $this->getAllBoardIDs();

                foreach ($board_ids as $id) {
                    $this->copyToBoardConfig($id, $new_board_settings);
                }

                $new_site_textareas = ['description'];
                $new_board_textareas = ['description'];

                foreach ($new_site_textareas as $setting_name) {
                    $prepared = nel_database('core')->prepare(
                        'UPDATE "' . NEL_SETTINGS_TABLE .
                        '" SET "input_attributes" = :textarea WHERE "setting_name" = :setting_name AND "setting_category" = \'site\'');
                    $prepared->bindValue(':textarea', '{"type":"textarea"}', PDO::PARAM_STR);
                    $prepared->bindValue(':setting_name', $setting_name);
                    nel_database('core')->executePrepared($prepared, null);
                }

                foreach ($new_board_textareas as $setting_name) {
                    $prepared = nel_database('core')->prepare(
                        'UPDATE "' . NEL_SETTINGS_TABLE .
                        '" SET "input_attributes" = :textarea WHERE "setting_name" = :setting_name AND "setting_category" = \'board\'');
                    $prepared->bindValue(':textarea', '{"type":"textarea"}', PDO::PARAM_STR);
                    $prepared->bindValue(':setting_name', $setting_name);
                    nel_database('core')->executePrepared($prepared, null);
                }

                echo ' - ' . __('Settings and board config tables updated.') . '<br>';

                // Update thread tables
                $db_prefixes = nel_database('core')->executeFetchAll(
                    'SELECT "db_prefix" FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);

                foreach ($db_prefixes as $prefix) {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_threads' . '" ADD COLUMN regen_cache SMALLINT NOT NULL DEFAULT 0');
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_threads' . '" ADD COLUMN cache TEXT DEFAULT NULL');
                }

                echo ' - ' . __('Thread tables updated.') . '<br>';

                $migration_count ++;

            case 'v0.9.26':
                // Update post tables
                $db_prefixes = nel_database('core')->executeFetchAll(
                'SELECT "db_prefix" FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);

                foreach ($db_prefixes as $prefix) {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_posts' . '" ADD COLUMN visitor_id VARCHAR(128) NOT NULL');
                }

                echo ' - ' . __('Post tables updated.') . '<br>';

                // Update bans table
                nel_database('core')->exec(
                    'ALTER TABLE "' . NEL_BANS_TABLE . '" ADD COLUMN visitor_id VARCHAR(128) NOT NULL');

                echo ' - ' . __('Bans table updated.') . '<br>';

                // Update logs table
                nel_database('core')->exec(
                    'ALTER TABLE "' . NEL_LOGS_TABLE . '" ADD COLUMN visitor_id VARCHAR(128) NOT NULL');

                echo ' - ' . __('Logs table updated.') . '<br>';

                // Update reports table
                nel_database('core')->exec(
                    'ALTER TABLE "' . NEL_REPORTS_TABLE . '" ADD COLUMN visitor_id VARCHAR(128) NOT NULL');

                echo ' - ' . __('Reports table updated.') . '<br>';

                $migration_count ++;
        }

        return $migration_count;
    }

    private function copyToSiteConfig(array $setting_names): void
    {
        $prepared = nel_database('core')->prepare(
            'INSERT INTO "' . NEL_SITE_CONFIG_TABLE .
            '" ("setting_name", "setting_value") SELECT "setting_name", "default_value" FROM "' . NEL_SETTINGS_TABLE .
            '" WHERE "setting_name" = ? AND "setting_category" = \'site\'');

        foreach ($setting_names as $name) {
            $prepared->bindValue(1, $name, PDO::PARAM_STR);
            nel_database('core')->executePrepared($prepared);
        }
    }

    private function copyToBoardConfig(string $board_id, array $setting_names): void
    {
        $prepared = nel_database('core')->prepare(
            'INSERT INTO "' . NEL_BOARD_CONFIGS_TABLE . '" ("board_id", "setting_name", "setting_value") SELECT \'' .
            $board_id . '\', "setting_name", "setting_value" FROM "' . NEL_BOARD_DEFAULTS_TABLE .
            '" WHERE "setting_name" = ?');

        foreach ($setting_names as $name) {
            $prepared->bindValue(1, $name, PDO::PARAM_STR);
            nel_database('core')->executePrepared($prepared);
        }
    }

    private function getAllBoardIDs(): array
    {
        $query = 'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"';
        $board_ids = nel_database('core')->executeFetchAll($query, PDO::FETCH_COLUMN);
        return $board_ids;
    }
}