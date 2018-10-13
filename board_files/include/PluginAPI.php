<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class PluginAPI
{
    private static $api_revision = 1;
    private static $hooks = array();
    private static $plugins = array();

    public static function apiRevision()
    {
        return self::$api_revision;
    }

    public static function registerPlugin($plugin_directory)
    {
        if (!ENABLE_PLUGINS)
        {
            return false;
        }

        $plugin_id = self::generateID();

        if (isset(self::$plugins[$plugin_id]))
        {
            return false;
        }

        self::$plugins[$plugin_id] = new \Nelliel\Plugin($plugin_id, $plugin_directory);
        return $plugin_id;
    }

    private static function verifyOrCreateHook($hook_name, $new = true)
    {
        if (!self::isValidHook($hook_name))
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
    public static function registerFunction($hook_name, $function_name, $plugin_id, $priority = 10)
    {
        if (!self::isValidPlugin($plugin_id))
        {
            return false;
        }

        self::verifyOrCreateHook($hook_name);
        self::$hooks[$hook_name]->registerFunction($function_name, $plugin_id, $priority);
        return true;
    }

    // Register hook methods here
    public static function registerMethod($hook_name, $class, $method_name, $plugin_id, $priority = 10)
    {
        if (!self::isValidPlugin($plugin_id))
        {
            return false;
        }

        self::verifyOrCreateHook($hook_name);
        self::$hooks[$hook_name]->registerMethod($class, $method_name, $plugin_id, $priority);
        return true;
    }

    public static function unregisterFunction($hook_name, $function_name, $plugin_id)
    {
        if (!self::isValidHook($hook_name) || !self::isValidPlugin($plugin_id))
        {
            return false;
        }

        self::$hooks[$hook_name]->unregisterFunction($function_name, $plugin_id);
        return true;
    }

    public static function unregisterMethod($hook_name, $class, $method_name, $plugin_id)
    {
        if (!self::isValidHook($hook_name) || !self::isValidPlugin($plugin_id))
        {
            return false;
        }

        self::$hooks[$hook_name]->unregisterFunction($class, $method_name, $plugin_id);
        return true;
    }

    public static function processHook($hook_name, $args, $returnable = null)
    {
        if (!ENABLE_PLUGINS || !self::isValidHook($hook_name))
        {
            return $returnable;
        }

        $returnable = self::$hooks[$hook_name]->process($args, $returnable);
        return $returnable;
    }

    public static function loadPlugins()
    {
        if (!ENABLE_PLUGINS)
        {
            return;
        }

        $file_handler = new \Nelliel\FileHandler();
        $plugin_files = $file_handler->recursiveFileList(PLUGINS_PATH);

        foreach ($plugin_files as $file)
        {
            if($file->getFilename() === 'nelliel-plugin.ini')
            {
                $parsed_ini = parse_ini_file($file->getPathname());
                $plugin_base_path = $file->getPathInfo()->getRealPath();
                include_once $plugin_base_path . '/' . $parsed_ini['initializer'];
            }
        }
    }

    private static function generateID()
    {
        return substr(md5(random_bytes(16)), -8);
    }

    private static function isValidHook($hook_name)
    {
        return isset(self::$hooks[$hook_name]) && self::$hooks[$hook_name] instanceof PluginHook;
    }

    private static function isValidPlugin($plugin_id)
    {
        return isset(self::$plugins[$plugin_id]);
    }
}