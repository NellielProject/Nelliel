<?php
declare(strict_types = 1);

namespace Nelliel\Setup;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Tables\TableBoardDefaults;
use Nelliel\Tables\TableSettings;
use Nelliel\Utility\FileHandler;
use PDO;

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
                    $prepared->bindValue(':mimetypes', $new_value);
                    $prepared->bindValue(':format', $data['format']);
                    nel_database('core')->executePrepared($prepared, null);
                }

                $prepared = nel_database('core')->exec(
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
                $setting_names = nel_database('core')->executeFetchAll(
                    'SELECT "setting_name" FROM "' . NEL_SETTINGS_TABLE . '"', PDO::FETCH_COLUMN);
                $ui_name_updates = ['ui_hide_thread', 'ui_show_thread', 'ui_hide_post', 'ui_show_post', 'ui_hide_file',
                    'ui_show_file', 'ui_cite_post', 'ui_reply_to_thread', 'ui_more_file_info', 'ui_less_file_info',
                    'ui_expand_thread', 'ui_collapse_thread'];
                $new_settings = ['ui_content_delimiter_left', 'ui_content_delimiter_right', 'ui_mod_delimiter_left',
                    'ui_mod_delimiter_right', 'ui_content_hide_embed', 'ui_content_show_embed'];
                $board_ids = nel_database('core')->executeFetchAll(
                    'SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);

                foreach ($setting_names as $name) {
                    if (in_array($name, $ui_name_updates)) {
                        $prepared = nel_database('core')->prepare(
                            'UPDATE "' . NEL_SETTINGS_TABLE .
                            '" SET "setting_name" = :new_name WHERE "setting_name" = :old_name');
                        $prepared->bindValue(':new_name', str_replace('ui_', 'ui_content_', $name));
                        $prepared->bindValue(':old_name', $name);
                        nel_database('core')->executePrepared($prepared, null);

                        $prepared = nel_database('core')->prepare(
                            'UPDATE "' . NEL_BOARD_DEFAULTS_TABLE .
                            '" SET "setting_name" = :new_name WHERE "setting_name" = :old_name');
                        $prepared->bindValue(':new_name', str_replace('ui_', 'ui_content_', $name));
                        $prepared->bindValue(':old_name', $name);
                        nel_database('core')->executePrepared($prepared, null);

                        $prepared = nel_database('core')->prepare(
                            'UPDATE "' . NEL_BOARD_CONFIGS_TABLE .
                            '" SET "setting_name" = :new_name WHERE "setting_name" = :old_name');
                        $prepared->bindValue(':new_name', str_replace('ui_', 'ui_content_', $name));
                        $prepared->bindValue(':old_name', $name);
                        nel_database('core')->executePrepared($prepared, null);
                    }

                    if (in_array($name, $new_settings)) {
                        $prepared = nel_database('core')->prepare(
                            'SELECT "setting_value" FROM "' . NEL_BOARD_DEFAULTS_TABLE .
                            '" WHERE "setting_name" = :setting_name');
                        $prepared->bindValue(':setting_name', $name, PDO::PARAM_STR);
                        $default = nel_database('core')->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

                        $prepared = nel_database('core')->prepare(
                            'INSERT INTO "' . NEL_BOARD_CONFIGS_TABLE .
                            '" ("board_id", "setting_name", "setting_value") VALUES (?, ?, ?)');

                        foreach ($board_ids as $board_id) {
                            $prepared->bindValue(1, $board_id, PDO::PARAM_STR);
                            $prepared->bindValue(2, $name, PDO::PARAM_STR);
                            $prepared->bindValue(3, $default, PDO::PARAM_STR);
                            nel_database('core')->executePrepared($prepared);
                        }
                    }
                }

                $settings_table = new TableSettings(nel_database('core'), nel_utilities()->sqlCompatibility());
                $settings_table->insertDefaults();
                $board_defaults_table = new TableBoardDefaults(nel_database('core'), nel_utilities()->sqlCompatibility());
                $board_defaults_table->insertDefaults();
                echo ' - ' . __('Settings and board config tables updated.') . '<br>';

                // Update thread tables
                $db_prefixes = nel_database('core')->executeFetchAll(
                    'SELECT "db_prefix" FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);

                foreach ($db_prefixes as $prefix) {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_threads' . '" ADD COLUMN regen_cache SMALLINT NOT NULL DEFAULT 0');
                    nel_database('core')->exec(
                        'ALTER TABLE "' . $prefix . '_threads' . '" ADD COLUMN cache TEXT DEFAULT NULL');
                    nel_database('core')->executePrepared($prepared);
                }

                echo ' - ' . __('Thread tables updated.') . '<br>';

                $migration_count ++;
                break;
        }

        return $migration_count;
    }
}