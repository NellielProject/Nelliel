<?php
declare(strict_types = 1);

namespace Nelliel\Setup;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Utility\FileHandler;
use PDO;
use Nelliel\Tables\TableSettings;
use Nelliel\Tables\TableBoardDefaults;

class Upgrade
{
    private $file_handler;
    private $original = 'v0.0';
    private $installed = 'v0.0';

    function __construct(FileHandler $file_handler)
    {
        $this->file_handler = $file_handler;
    }

    public function displayLogin(): void
    {
        echo '
<!DOCTYPE html>
<html>
<head>
    <title>' . __('Site owner login') . '</title>
</head>
<body>
    <p>' . __('Please log in with the site owner account to perform upgrades.') .
            '</p>
    <form accept-charset="utf-8" action="imgboard.php?upgrade" method="post">
        <input type="hidden" name="upgrade_login" value="">
        <div>
            <label for="username">' . __('Username:') .
            '</label>
            <input id="username" type="text" name="username" maxlength="255">
        </div>
        <div>
            <label for="super_sekrit">' . __('Password:') .
            '</label>
            <input id="super_sekrit" type="password" name="super_sekrit" maxlength="255">
        </div>
        <div>
            <input type="submit" value="' . __('Submit') . '">
        </div>
    </form>
</body></html>';
    }

    public function verifyLogin(): bool
    {
        $username = $_POST['username'] ?? '';
        $form_password = $_POST['super_sekrit'] ?? '';
        $prepared = nel_database('core')->prepare(
            'SELECT * FROM "' . NEL_USERS_TABLE . '" WHERE "username" = :username AND "owner" = 1');
        $prepared->bindValue(':username', $username);
        $user_data = nel_database('core')->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);
        return is_array($user_data) &&
            nel_password_verify($form_password, $user_data['password'] ?? $user_data['user_password']);
    }

    public function doUpgrades(): void
    {
        if (!$this->needsUpgrade()) {
            echo __('Already up to date!');
            return;
        }

        if (isset($_POST['upgrade_login'])) {
            if (!$this->verifyLogin()) {
                echo __('Username or password is wrong or that user is not a site owner account.');
                return;
            }
        } else {
            $this->displayLogin();
            return;
        }

        $migration_count = $this->doMigrations();

        if ($migration_count > 0) {
            echo sprintf(__('%d migrations were completed.'), $migration_count) . '<br>';
        } else {
            echo __('No migrations were needed.') . '<br>';
        }

        $generate_files = new GenerateFiles($this->file_handler);
        $versions_data = array();
        $versions_data['original'] = $this->originalVersion();
        $versions_data['installed'] = NELLIEL_VERSION;
        $generate_files->versions($versions_data, true);
        echo __('Upgrades completed!');
    }

    public function needsUpgrade(): bool
    {
        return version_compare($this->installedVersion(), NELLIEL_VERSION, '<');
    }

    public function originalVersion(): string
    {
        if ($this->original === 'v0.0') {
            $versions_data = $this->versionsData();
            $this->original = $versions_data['original'] ?? $this->original;
        }

        return $this->original;
    }

    public function installedVersion(): string
    {
        if ($this->installed === 'v0.0') {
            $versions_data = $this->versionsData();
            $this->installed = $versions_data['installed'] ?? $this->installed;
        }

        return $this->installed;
    }

    public function versionsData(): array
    {
        $versions_data = array();

        if (file_exists(NEL_GENERATED_FILES_PATH . 'versions.php')) {
            include NEL_GENERATED_FILES_PATH . 'versions.php';
        }

        return $versions_data;
    }

    public function doMigrations(): int
    {
        $migration_count = 0;

        switch ($this->installedVersion()) {
            case 'v0.9.25':
                $target_version = NELLIEL_VERSION;
                echo sprintf(__('Updating from v0.9.25 to %s...'), $target_version) . '<br>';
                $core_sqltype = nel_database('core')->config()['sqltype'];

                // Update filetypes table
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    nel_database('core')->exec(
                        'ALTER TABLE "' . NEL_FILETYPES_TABLE . '" CHANGE mime mimetypes TEXT NOT NULL');
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
                        'ALTER TABLE "' . NEL_USERS_TABLE . '" CHANGE user_password password VARCHAR(255) NOT NULL');
                } else {
                    nel_database('core')->exec('ALTER TABLE "' . NEL_USERS_TABLE . '" RENAME user_password TO password');
                }

                echo ' - ' . __('Users table updated.') . '<br>';

                // Update archive table
                if ($core_sqltype === 'MYSQL' || $core_sqltype === 'MARIADB') {
                    $prefixes = nel_database('core')->executeFetchAll(
                        'SELECT "db_prefix" FROM "' . NEL_BOARD_DATA_TABLE . '"', PDO::FETCH_COLUMN);

                    foreach ($prefixes as $prefix) {
                        nel_database('core')->exec(
                            'ALTER TABLE "' . $prefix . '_archives' . '" MODIFY thread_data LONGTEXT NOT NULL');
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

                $migration_count ++;
        }

        return $migration_count;
    }
}