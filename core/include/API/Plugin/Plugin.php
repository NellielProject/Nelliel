<?php
declare(strict_types = 1);

namespace Nelliel\API\Plugin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use Nelliel\Utility\FileHandler;
use PDO;

class Plugin
{
    private NellielPDO $database;
    private string $plugin_id = '';
    private bool $enabled = false;
    private array $info = array();
    private string $initializer = '';
    private string $directory = '';
    private array $settings = array();
    private bool $settings_loaded = false;

    function __construct(NellielPDO $database, string $plugin_id)
    {
        $this->database = $database;
        $this->plugin_id = $plugin_id;
        $this->loadData();
    }

    /**
     * Returns the string ID of the plugin.
     */
    public function id(): string
    {
        return $this->plugin_id;
    }

    /**
     * Check if the plugin is enabled.
     */
    public function enabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Returns plugin information matching the given key.
     */
    public function info(string $key): string
    {
        return $this->info[$key] ?? '';
    }

    /**
     * Gets the sub-directory where the plugin files are located.
     */
    public function directory(): string
    {
        return $this->directory;
    }

    /**
     * Provides the full file path for the initializer.
     */
    public function initializerFile(): string
    {
        return NEL_PLUGINS_FILES_PATH . $this->directory . '/' . $this->initializer;
    }

    /**
     * Installs the plugin.
     */
    public function install(bool $reinstall = false): void
    {
        $file_handler = new FileHandler();
        $plugin_files = $file_handler->recursiveFileList(NEL_PLUGINS_FILES_PATH);
        $parsed_ini = '';
        $directory = '';
        $initializer_file = '';
        $installer_file = '';

        foreach ($plugin_files as $file) {
            if ($file->getFilename() === 'nelliel-plugin.ini') {
                $parsed_ini = parse_ini_file($file->getPathname(), true);

                if (!isset($parsed_ini['id'])) {
                    continue;
                }

                if ($parsed_ini['id'] !== $this->plugin_id) {
                    continue;
                }

                $directory = basename(dirname($file->getRealPath()));
                $initializer_file = $parsed_ini['initializer'] ?? '';
                $installer_file = $parsed_ini['installer'] ?? '';
                break;
            }
        }

        $encoded_ini = json_encode($parsed_ini);

        if ($this->database->rowExists(NEL_PLUGINS_TABLE, ['plugin_id'], [$this->id()],
            [PDO::PARAM_STR, PDO::PARAM_STR])) {
            if (!$reinstall) {
                return;
            }

            $this->uninstall();
        }

        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_PLUGINS_TABLE .
            '" ("plugin_id", "directory", "initializer", "parsed_ini", "enabled") VALUES (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared, [$this->id(), $directory, $initializer_file, $encoded_ini, 1]);
        $this->loadData();

        if (!nel_true_empty($installer_file) && file_exists(NEL_PLUGINS_FILES_PATH . $directory . '/' . $installer_file)) {
            include NEL_PLUGINS_FILES_PATH . $directory . '/' . $installer_file;
        }
    }

    /**
     * Uninstalls the plugin.
     */
    public function uninstall(): void
    {
        $uninstaller_file = $this->info['uninstaller'] ?? '';

        if (!nel_true_empty($uninstaller_file) &&
            file_exists(NEL_PLUGINS_FILES_PATH . $this->directory . '/' . $uninstaller_file)) {
            include NEL_PLUGINS_FILES_PATH . $this->directory . '/' . $uninstaller_file;
        }

        $prepared = $this->database->prepare('DELETE FROM "' . NEL_PLUGINS_TABLE . '" WHERE "plugin_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    /**
     * Loads the plugin data.
     */
    private function loadData(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_PLUGINS_TABLE . '" WHERE "plugin_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);

        if (!is_array($data)) {
            return;
        }

        $this->initializer = $data['initializer'] ?? '';
        $this->directory = $data['directory'] ?? '';
        $this->info = json_decode($data['parsed_ini'] ?? '', true);
        $this->enabled = boolval($data['enabled'] ?? 0);
    }

    /**
     * Enables the plugin for loading on script startup.
     */
    public function enable(): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_PLUGINS_TABLE . '" SET "enabled" = 1 WHERE "plugin_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    /**
     * Disables the plugin without uninstalling it.
     */
    public function disable(): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_PLUGINS_TABLE . '" SET "enabled" = 0 WHERE "plugin_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function setting(string $setting = null, string $domain_id = Domain::SITE)
    {
        if (empty($this->settings)) {
            $this->loadSettings();
        }

        if (is_null($setting)) {
            return $this->settings;
        }

        return $this->settings[$setting]['domains'][$domain_id] ?? $this->settings[$setting]['default_value'] ?? null;
    }

    private function loadSettings(bool $reload = false): void
    {
        if ($this->settings_loaded && !$reload) {
            return;
        }

        $settings = array();
        $prepared = $this->database->prepare(
            'SELECT "setting_name", "default_value", "data_type" FROM "' . NEL_SETTINGS_TABLE .
            '" WHERE "setting_category" = \'plugin\' AND "setting_owner" = :plugin_id');
        $prepared->bindValue(':plugin_id', $this->plugin_id, PDO::PARAM_STR);
        $settings_list = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);

        $prepared = $this->database->prepare(
            'SELECT "board_id", "setting_name", "setting_value" FROM "' . NEL_PLUGIN_CONFIGS_TABLE .
            '" WHERE "plugin_id" = :plugin_id');
        $prepared->bindValue(':plugin_id', $this->plugin_id, PDO::PARAM_STR);
        $configs = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);

        $config_list = array();

        foreach ($configs as $config) {
            $config_list[$config['setting_name']][$config['board_id']] = $config;
        }

        foreach ($settings_list as $setting) {
            $settings[$setting['setting_name']]['default_value'] = nel_typecast($setting['default_value'],
                $setting['data_type'], false);
            $domain_config = $config_list[$setting['setting_name']] ?? array();

            foreach ($domain_config as $domain_id => $config) {
                $settings[$setting['setting_name']]['domains'][$domain_id] = nel_typecast(
                    $config['setting_value'] ?? $setting['default_value'], $setting['data_type'], false);
            }
        }

        $this->settings = $settings;
        $this->settings_loaded = true;
    }
}
