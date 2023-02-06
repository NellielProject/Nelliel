<?php
declare(strict_types = 1);

namespace Nelliel\API\Plugin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\INIParser;
use Nelliel\Database\NellielPDO;
use PDO;

class PluginAPI
{
    public const API_VERSION = 1; // Only updates on breaking changes
    private $database;
    private static $hooks = array();
    private static $loaded_plugins = array();
    private static $loaded_plugin_ids = array();
    private $ini_parser;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
        $this->ini_parser = new INIParser(nel_utilities()->fileHandler());
    }

    /**
     * Gets a new Plugin instance.
     */
    public function getPlugin(string $plugin_id): Plugin
    {
        $plugin = new Plugin($this->database, $plugin_id);
        return $plugin;
    }

    /**
     * Checks if a plugin has been loaded.
     */
    public function pluginLoaded(string $plugin_id): bool
    {
        return isset(self::$loaded_plugins[$plugin_id]);
    }

    /**
     * Verifies if hook is valid. If it does not exist, creates the hook.
     */
    private function verifyOrCreateHook(string $hook_name, bool $new = true): bool
    {
        if (!$this->isValidHook($hook_name)) {
            if ($new) {
                self::$hooks[$hook_name] = new PluginHook($hook_name);
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Registers a function to the specified hook.
     */
    public function addFunction(string $hook_name, string $function_name, string $plugin_id, int $priority = 10): bool
    {
        if (!$this->isValidPlugin($plugin_id)) {
            return false;
        }

        $this->verifyOrCreateHook($hook_name);
        return self::$hooks[$hook_name]->addFunction($function_name, $plugin_id, $priority);
    }

    /**
     * Registers a method to the specified hook.
     */
    public function addMethod(string $hook_name, object $class, string $method_name, string $plugin_id,
        int $priority = 10): bool
    {
        if (!$this->isValidPlugin($plugin_id)) {
            return false;
        }

        $this->verifyOrCreateHook($hook_name);
        return self::$hooks[$hook_name]->addMethod($class, $method_name, $plugin_id, $priority);
    }

    /**
     * Removes a function from the specified hook.
     */
    public function removeFunction(string $hook_name, string $function_name, string $plugin_id, int $priority = 10): bool
    {
        if (!$this->isValidHook($hook_name) || !$this->isValidPlugin($plugin_id)) {
            return false;
        }

        return self::$hooks[$hook_name]->removeFunction($function_name, $plugin_id, $priority);
    }

    /**
     * Removes a method from the specified hook.
     */
    public function removeMethod(string $hook_name, object $class, string $method_name, string $plugin_id,
        int $priority = 10): bool
    {
        if (!$this->isValidHook($hook_name) || !$this->isValidPlugin($plugin_id)) {
            return false;
        }

        return self::$hooks[$hook_name]->removeMethod($class, $method_name, $plugin_id, $priority);
    }

    /**
     * Processes all functions and methods registered to the specified hook.
     */
    public function processHook(string $hook_name, array $args, $returnable = null)
    {
        if (!NEL_ENABLE_PLUGINS || !$this->isValidHook($hook_name)) {
            return $returnable;
        }

        return self::$hooks[$hook_name]->process($args, $returnable);
    }

    /**
     * Gets all plugins currently in the plugins directory.
     *
     * @return (Plugin|string)[]
     */
    public function getAvailablePlugins(bool $id_only = false): array
    {
        $inis = $this->ini_parser->parseDirectories(NEL_PLUGINS_FILES_PATH, 'nelliel-plugin.ini');
        $plugins = array();

        foreach ($inis as $ini) {
            if(!isset($ini['id'])) {
                continue;
            }

            if ($id_only) {
                $plugins[] = $ini['id'];
            } else {
                $plugins[] = $this->getPlugin($ini['id']);
            }
        }

        return $plugins;
    }

    /**
     * Gets all plugins currently installed.
     *
     * @return (Plugin|string)[]
     */
    public function getInstalledPlugins(bool $id_only = false): array
    {
        $query = 'SELECT "plugin_id" FROM "' . NEL_PLUGINS_TABLE . '"';
        $plugin_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);

        if ($id_only) {
            return $plugin_ids;
        }

        $plugins = array();

        foreach ($plugin_ids as $id) {
            $plugins[] = $this->getPlugin($id);
        }

        return $plugins;
    }

    /**
     * Gets all plugins currently loaded.
     *
     * @return (Plugin|string)[]
     */
    public function getLoadedPlugins(bool $id_only = false): array
    {
        if ($id_only) {
            return self::$loaded_plugin_ids;
        }

        return self::$loaded_plugins;
    }

    /**
     * Loads all enabled plugins.
     */
    public function loadPlugins(): void
    {
        if (!NEL_ENABLE_PLUGINS) {
            return;
        }

        $plugins = $this->getInstalledPlugins();
        $enabled_plugins = array();
        $enabled_plugin_ids = array();

        foreach ($plugins as $plugin) {
            if (!$plugin->enabled()) {
                continue;
            }

            $enabled_plugin_ids[] = $plugin->id();
            $enabled_plugins[$plugin->id()] = $plugin;
        }

        foreach ($enabled_plugins as $plugin) {
            $min_php = $plugin->info('min_php');

            if ($min_php !== '' && !version_compare(PHP_VERSION, $min_php, '>=')) {
                continue;
            }

            $min_nelliel = $plugin->info('min_nelliel');

            if ($min_nelliel !== '' && !version_compare(NELLIEL_VERSION, $min_nelliel, '>=')) {
                continue;
            }

            $api_version = $plugin->info('api_version');

            if ($api_version !== '' && $api_version != self::API_VERSION) {
                continue;
            }

            $dependencies = array_map('trim', explode(',', $plugin->info('dependencies')));
            $load = true;

            foreach ($dependencies as $dependency) {
                if ($dependency !== '' && !in_array($dependency, $enabled_plugin_ids)) {
                    $load = false;
                    break;
                }
            }

            if ($load) {
                if (!file_exists($plugin->initializerFile())) {
                    continue;
                }

                self::$loaded_plugin_ids[] = $plugin->id();
                self::$loaded_plugins[$plugin->id()] = $plugin;
                include_once $plugin->initializerFile();
                $this->processHook('nel-in-after-plugin-loaded', [$plugin->id()]);
            }
        }

        $this->processHook('nel-in-after-all-plugins-loaded', []);
    }

    /**
     * Check that the specified hook ia valid.
     */
    private function isValidHook(string $hook_name): bool
    {
        return isset(self::$hooks[$hook_name]) && self::$hooks[$hook_name] instanceof PluginHook;
    }

    /**
     * Check that the specified plugin is valid.
     */
    private function isValidPlugin(string $plugin_id): bool
    {
        return isset(self::$loaded_plugins[$plugin_id]);
    }
}