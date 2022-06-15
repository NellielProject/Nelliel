<?php
declare(strict_types = 1);

namespace Nelliel\API\Plugin;

use Nelliel\NellielPDO;
use PDO;
use Nelliel\INIParser;
defined('NELLIEL_VERSION') or die('NOPE.AVI');

class PluginAPI
{
    private $database;
    private static $api_revision = 1;
    private static $hooks = array();
    private static $plugins = array();
    private $ini_parser;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
        $this->ini_parser = new INIParser(nel_utilities()->fileHandler());
    }

    public function apiRevision()
    {
        return self::$api_revision;
    }

    public function getPlugin(string $id): Plugin
    {
        $plugin = new Plugin($this->database, $id);
        return $plugin;
    }

    public function pluginLoaded(string $id_string)
    {
        return isset(self::$plugins[$id_string]);
    }

    private function verifyOrCreateHook(string $hook_name, bool $new = true)
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

    // Register hook functions here
    public function addFunction(string $hook_name, string $function_name, string $plugin_id, int $priority = 10)
    {
        if (!$this->isValidPlugin($plugin_id)) {
            return false;
        }

        $this->verifyOrCreateHook($hook_name);
        self::$hooks[$hook_name]->addFunction($function_name, $plugin_id, $priority);
        return true;
    }

    // Register hook methods here
    public function addMethod(string $hook_name, $class, string $method_name, string $plugin_id, int $priority = 10)
    {
        if (!$this->isValidPlugin($plugin_id)) {
            return false;
        }

        $this->verifyOrCreateHook($hook_name);
        self::$hooks[$hook_name]->addMethod($class, $method_name, $plugin_id, $priority);
        return true;
    }

    public function removeFunction(string $hook_name, string $function_name, string $plugin_id)
    {
        if (!$this->isValidHook($hook_name) || !$this->isValidPlugin($plugin_id)) {
            return false;
        }

        self::$hooks[$hook_name]->removeFunction($function_name, $plugin_id);
        return true;
    }

    public function removeMethod(string $hook_name, $class, string $method_name, string $plugin_id)
    {
        if (!$this->isValidHook($hook_name) || !$this->isValidPlugin($plugin_id)) {
            return false;
        }

        self::$hooks[$hook_name]->removeFunction($class, $method_name, $plugin_id);
        return true;
    }

    public function processHook(string $hook_name, array $args, $returnable = null)
    {
        if (!NEL_ENABLE_PLUGINS || !$this->isValidHook($hook_name)) {
            return $returnable;
        }

        $returnable = self::$hooks[$hook_name]->process($args, $returnable);
        return $returnable;
    }

    public function getPluginInis(): array {
        return $this->ini_parser->parseDirectories(NEL_PLUGINS_FILES_PATH, 'nelliel-plugin.ini');
    }

    public function getInstalledPlugins(): array
    {
        $query = 'SELECT "plugin_id" FROM "' . NEL_PLUGINS_TABLE . '"';
        $plugin_ids = $this->database->executeFetchAll($query, PDO::FETCH_COLUMN);
        $plugins = array();

        foreach ($plugin_ids as $id) {
            $plugins[] = $this->getPlugin($id);
        }

        return $plugins;
    }

    public function loadPlugins(): void
    {
        if (!NEL_ENABLE_PLUGINS) {
            return;
        }

        $plugins = $this->getinstalledPlugins();

        foreach ($plugins as $plugin) {
            if ($plugin->enabled()) {
                self::$plugins[$plugin->id()] = $plugin;
                include_once $plugin->initializerFile();
            }
        }
    }

    private function generateID(): string
    {
        return utf8_substr(md5(random_bytes(16)), -8);
    }

    private function isValidHook(string $hook_name): bool
    {
        return isset(self::$hooks[$hook_name]) && self::$hooks[$hook_name] instanceof PluginHook;
    }

    private function isValidPlugin($plugin_id): bool
    {
        return isset(self::$plugins[$plugin_id]);
    }
}