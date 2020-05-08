<?php

namespace Nelliel\API\Plugin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class PluginAPI
{
    private static $api_revision = 1;
    private static $hooks = array();
    private static $plugins = array();
    private static $parsed_ini_files = array();
    private static $loaded_plugins = array();

    function __construct()
    {
    }

    public function apiRevision()
    {
        return self::$api_revision;
    }

    public function registerPlugin($plugin_directory, $initializer_file)
    {
        if (!NEL_ENABLE_PLUGINS)
        {
            return false;
        }

        if(array_key_exists($initializer_file, self::$parsed_ini_files))
        {
            $plugin_id = $this->generateID();
            $new_plugin = new Plugin($plugin_id, $plugin_directory, self::$parsed_ini_files[$initializer_file]);
            self::$loaded_plugins[$new_plugin->getIniValue('id_string')] = true;
            self::$plugins[$plugin_id] = $new_plugin;
            return $plugin_id;
        }

        return false;
    }

    public function pluginLoaded(string $id_string)
    {
        return isset(self::$loaded_plugins[$id_string]);
    }

    private function verifyOrCreateHook(string $hook_name, bool $new = true)
    {
        if (!$this->isValidHook($hook_name))
        {
            if ($new)
            {
                self::$hooks[$hook_name] = new PluginHook($hook_name);
            }
            else
            {
                return false;
            }
        }

        return true;
    }

    // Register hook functions here
    public function addFunction(string $hook_name, string $function_name, string $plugin_id, int $priority = 10)
    {
        if (!$this->isValidPlugin($plugin_id))
        {
            return false;
        }

        $this->verifyOrCreateHook($hook_name);
        self::$hooks[$hook_name]->addFunction($function_name, $plugin_id, $priority);
        return true;
    }

    // Register hook methods here
    public function addMethod(string $hook_name, $class, string $method_name, string $plugin_id, int $priority = 10)
    {
        if (!$this->isValidPlugin($plugin_id))
        {
            return false;
        }

        $this->verifyOrCreateHook($hook_name);
        self::$hooks[$hook_name]->addMethod($class, $method_name, $plugin_id, $priority);
        return true;
    }

    public function removeFunction(string $hook_name, string $function_name, string $plugin_id)
    {
        if (!$this->isValidHook($hook_name) || !$this->isValidPlugin($plugin_id))
        {
            return false;
        }

        self::$hooks[$hook_name]->removeFunction($function_name, $plugin_id);
        return true;
    }

    public function removeMethod(string $hook_name, $class, string $method_name, string $plugin_id)
    {
        if (!$this->isValidHook($hook_name) || !$this->isValidPlugin($plugin_id))
        {
            return false;
        }

        self::$hooks[$hook_name]->removeFunction($class, $method_name, $plugin_id);
        return true;
    }

    public function processHook(string $hook_name, array $args, $returnable = null)
    {
        if (!NEL_ENABLE_PLUGINS || !$this->isValidHook($hook_name))
        {
            return $returnable;
        }

        $returnable = self::$hooks[$hook_name]->process($args, $returnable);
        return $returnable;
    }

    public function loadPlugins()
    {
        if (!NEL_ENABLE_PLUGINS)
        {
            return;
        }

        $file_handler = new \Nelliel\Utility\FileHandler();
        $plugin_files = $file_handler->recursiveFileList(NEL_PLUGINS_FILES_PATH);

        foreach ($plugin_files as $file)
        {
            if($file->getFilename() === 'nelliel-plugin.ini')
            {
                $parsed_ini = parse_ini_file($file->getPathname(), true);
                $plugin_base_path = $file->getPathInfo()->getRealPath();
                $initializer_file = $plugin_base_path . '/' . $parsed_ini['initializer'];
                self::$parsed_ini_files[$initializer_file] = $parsed_ini;
                include_once $initializer_file;
            }
        }
    }

    private function generateID()
    {
        return substr(md5(random_bytes(16)), -8);
    }

    private function isValidHook(string $hook_name)
    {
        return isset(self::$hooks[$hook_name]) && self::$hooks[$hook_name] instanceof PluginHook;
    }

    private function isValidPlugin($plugin_id)
    {
        return isset(self::$plugins[$plugin_id]);
    }
}