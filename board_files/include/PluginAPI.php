<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class PluginAPI
{
    private $hooks = array();
    private $plugins = array();

    public function registerPlugin($plugin_info)
    {
        $plugin_id = $this->generateID($plugin_info);

        if (!in_array($plugin_id, $this->plugins))
        {
            $this->plugins[$plugin_id] = $plugin_info;
            return $plugin_id;
        }

        return false;
    }

    // Register hook functions here
    public function registerFunctionForHook($hook_name, $function_name, $plugin_id, $priority = 10)
    {
        if (!$this->isValidPlugin($plugin_id) || !$this->isValidFunction($function_name))
        {
            return false;
        }

        $hooks = $this->hooks[$hook_name][] = ['function_name' => $function_name, 'plugin_id' => $plugin_id,
            'priority' => $priority];
        $this->sort_hooks($hook_name);
        return true;
    }

    // Register hook methods here
    public function registerMethodForHook($hook_name, $class, $method_name, $plugin_id, $priority = 10)
    {
        if (!$this->isValidPlugin($plugin_id) || !$this->isValidMethod($class, $method_name))
        {
            return false;
        }

        $hooks = $this->hooks[$hook_name][] = ['class' => $class, 'method_name' => $method_name,
            'plugin_id' => $plugin_id, 'priority' => $priority];
        $this->sort_hooks($hook_name);
        return true;
    }

    public function unregisterFunctionForHook($hook_name, $function_name, $plugin_id)
    {
        if (!$this->isValidHook($hook_name) || !$this->isValidPlugin($plugin_id) ||
            !$this->isValidFunction($function_name))
        {
            return false;
        }

        $success = false;

        foreach ($this->hooks[$hook_name] as $key => $value)
        {
            $valid_registration = $this->verifyRegistrationArray($key, false);

            if ($valid_registration && $key['plugin_id'] === $plugin_id && $key['function_name'] === $function_name)
            {
                unset($this->hooks[$hook_name][$key]);
                $success = true;
                break;
            }
        }

        $this->sort_hooks($hook_name);
        return $success;
    }

    public function unregisterMethodForHook($hook_name, $class, $method_name, $plugin_id)
    {
        if (!$this->isValidHook($hook_name) || !$this->isValidPlugin($plugin_id) ||
            !$this->isValidMethod($class, $method_name))
        {
            return false;
        }

        $success = false;

        foreach ($this->hooks[$hook_name] as $key => $value)
        {
            $valid_registration = $this->verifyRegistrationArray($key, true);

            if ($valid_registration && $key['plugin_id'] === $plugin_id && $key['class'] === $class &&
                $key['method_name'] === $method_name)
            {
                unset($this->hooks[$hook_name][$key]);
                $success = true;
                break;
            }
        }

        $this->sort_hooks($hook_name);
        return $success;
    }

    public function processPluginHook($hook_name, $data)
    {
        if (!$this->isValidHook($hook_name))
        {
            return; // TODO return input if needed
        }

        $hook = $this->hooks[$hook_name];

        foreach ($hook as $registration)
        {
            $is_method = (isset($registration['method_name'])) ? true : false;

            if ($is_method && $this->isValidMethod($registration['class'], $registration['method_name']))
            {
                call_user_func_array(array($registration['class'], $registration['method_name']), $data);
            }
            else
            {
                call_user_func_array($registration['function_name'], $data);
            }
        }
    }

    private function getPluginIniFiles()
    {
        $file_handler = new \Nelliel\FileHandler();
        $files = $file_handler->recursiveFileList(PLUGINS_PATH, false, ['ini']);
        return $files;
    }

    public function initializePlugins()
    {
        $ini_files = $this->getPluginIniFiles();

        foreach ($ini_files as $ini_file)
        {
            $ini = parse_ini_file($ini_file);
            $plugin_root = pathinfo($ini_file, PATHINFO_DIRNAME) . '/';
            $initializer = $ini['initializer'];
            include_once $plugin_root . $initializer;
        }
    }

    private function generateID($plugin_info)
    {
        return substr(md5(implode('', $plugin_info) . time()), -8);
    }

    private function verifyRegistrationArray($array, $is_class)
    {
        if (!isset($array['plugin_id']))
        {
            return false;
        }

        if ($is_class)
        {
            return (isset($array['class']) && isset($array['method_name']));
        }
        else
        {
            return isset($array['function_name']);
        }
    }

    private function sort_hooks($hook_name)
    {
        usort($this->hooks[$hook_name], array($this, 'sort_by_priority'));
    }

    private function sort_by_priority($a, $b)
    {
        if ($a['priority'] == $b['priority'])
        {
            return $a['index'] - $b['index'];
        }

        return ($a['priority'] < $b['priority']) ? -1 : 1;
    }

    private function isValidHook($hook_name)
    {
        return !empty($hook_name) && isset($this->hooks[$hook_name]);
    }

    private function isValidPlugin($plugin_id)
    {
        return !empty($plugin_id) && isset($this->plugins[$plugin_id]);
    }

    private function isValidFunction($function_name)
    {
        return !empty($function_name) && function_exists($function_name);
    }

    private function isValidMethod($class, $method_name)
    {
        return !empty($class) && is_object($class) && method_exists($class, $method_name);
    }
}