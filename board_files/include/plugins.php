<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

$plugin_files = glob(PLUGINS_PATH . '*.nel.php');
$plugins = new plugin_handler();

foreach($plugin_files as $file)
{
    require_once $file;
}

$plugins->activate();

class plugin_handler
{
    private static $hooks= array();
    private static $plugins = array();
    private static $loaded = FALSE;

    // Returns TRUE if activation successful, FALSE if not (usually because activation was already done)
    public function activate()
    {
        if(self::$loaded)
        {
            return FALSE;
        }

        $this->sort_hooks();

        echo '<pre>';
        print_r(self::$hooks);
        print_r(self::$plugins);
        echo '</pre>';
        //die();
        self::$loaded = TRUE;
        return TRUE;
    }
    
    private function sort_hooks()
    {
        $hooks = self::$hooks;
        
        foreach($hooks as $key => $value)
        {
            usort($hooks[$key], array($this, 'sort_by_priority'));
        }

        self::$hooks = $hooks;
    }
    
    private function sort_by_priority($a, $b)
    {
        if ($a['priority'] == $b['priority'])
        {
            return $a['index'] - $b['index'];
        }
    
        return ($a['priority'] < $b['priority']) ? -1 : 1;
    }

    // Returns a unique plugin id (integer)
    public function register_plugin($name, $author, $version)
    {
        $plugins = self::$plugins;
        $plugin_id = 0;

        while(in_array($plugin_id, $plugins, TRUE) || $plugin_id === 0)
        {
            $plugin_id = mt_rand();
        }

        self::$plugins[$plugin_id] = array($name, $author, $version);
        return $plugin_id;
    }
    
    public function register_hook_function($hook_name, $function_name, $priority, $plugin_id)
    {
        $hooks = self::$hooks;

        if(!isset($hooks[$hook_name]))
        {
            $next_index = 0;
        }
        else
        {
            end($hooks[$hook_name]);
            $next_index = key($hooks[$hook_name]) + 1;
        }
    
        $hooks[$hook_name][] = array('function' => $function_name, 'priority' => $priority, 'index' => $next_index, 'plugin' => $plugin_id);
        self::$hooks = $hooks;
    }
    
    public function unregister_hook_function($hook_name, $function_name, $priority, $plugin_id)
    {
        $hooks = self::$hooks;

        foreach($hooks[$hook_name] as $key => $value)
        {
            if(in_array($function_name, $hooks[$hook_name][$key], TRUE))
            {
                unset($hooks[$hook_name][$key]);
            }
        }
        
        self::$hooks = $hooks;
        $this->sort_hooks();
    }
    
    public function plugin_hook($hook_name, $return_input, $input)
    {
        $hooks = self::$hooks;
        $return = NULL;

        if(isset($hooks[$hook_name]))
        {
            foreach($hooks[$hook_name] as $hook_function)
            {
                if(function_exists($hook_function['function']))
                {
                    $return = call_user_func_array($hook_function['function'], $input);

                    if(!is_null($return) && $return_input)
                    {
                        $input = array($return);
                    }
                }
            }
        }

        if(is_null($return) && $return_input)
        {
            $return = $input;
        }

        return $return;
    }
}
?>