<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class nel_plugin_handler
{
    private $hooks = array();
    private $plugins = array();
    private $loaded = FALSE;
    
    // Returns TRUE if activation successful, FALSE if not (usually because activation was already done)
    public function activate()
    {
        if ($this->loaded)
        {
            return FALSE;
        }

        $this->sort_hooks();
        $this->loaded = TRUE;
        return TRUE;
    }

    private function sort_hooks()
    {
        $hooks = $this->hooks;
        
        foreach ($hooks as $key => $value)
        {
            usort($hooks[$key], array($this, 'sort_by_priority'));
        }
        
        $this->hooks = $hooks;
    }

    private function sort_by_priority($a, $b)
    {
        if ($a['priority'] == $b['priority'])
        {
            return $a['index'] - $b['index'];
        }
        
        return ($a['priority'] < $b['priority']) ? -1 : 1;
    }
    
    // Returns a plugin id hash or FALSE if plugin already registered
    public function register_plugin($instance, $name, $author, $version)
    {
        $plugin_id = $this->plugin_id_hash($name, $author, $version);
        
        if (!in_array($plugin_id, $this->plugins, TRUE))
        {
            $this->plugins[$plugin_id] = array('instance' => $instance,
                                                'name' => $name,
                                                'author' => $author,
                                                'version' => $version);
            return $plugin_id;
        }
        else
        {
            return FALSE;
        }
    }
    
    // Returns plugin id hash
    public function plugin_id_hash($a, $b, $c)
    {
        return md5($a . $b . $c);
    }
    
    // Register hook functions here
    public function register_hook_function($hook_name, $function_name, $priority, $plugin_id)
    {
        $hooks = $this->hooks;
        
        if (!isset($hooks[$hook_name]))
        {
            $next_index = 0;
        }
        else
        {
            end($hooks[$hook_name]);
            $next_index = key($hooks[$hook_name]) + 1;
        }
        
        $hooks[$hook_name][] = array('function' => $function_name, 'priority' => $priority, 'index' => $next_index, 'plugin' => $plugin_id);
        $this->hooks = $hooks;
    }
    
    // Unregister hookk functions here. Returns TRUE is successful, FALSE if not
    public function unregister_hook_function($hook_name, $function_name, $priority, $plugin_id)
    {
        $hooks = $this->hooks;
        
        foreach ($hooks[$hook_name] as $key => $value)
        {
            if (in_array($function_name, $hooks[$hook_name][$key], TRUE))
            {
                if (in_array($plugin_id, $hooks[$hook_name][$key], TRUE))
                {
                    unset($hooks[$hook_name][$key]);
                }
                else
                {
                    return FALSE;
                }
            }
            else
            {
                return FALSE;
            }
        }
        
        $this->hooks = $hooks;
        $this->sort_hooks();
        return TRUE;
    }

    public function plugin_hook($hook_name, $return_input, $input)
    {
        $hooks = $this->hooks;
        $return = $input[0];
        
        if (isset($hooks[$hook_name]))
        {
            foreach ($hooks[$hook_name] as $hook_function)
            {
                if(is_object($hook_function['function'][0])
                    && method_exists($hook_function['function'][0], $hook_function['function'][1]))
                {
                    $return = call_user_func_array(array($hook_function['function'][0], $hook_function['function'][1]), $input);
                }
                else if(function_exists($hook_function['function'][1]))
                {
                    $return = call_user_func_array($hook_function['function'][1], $input);
                    
                    if (!is_null($return) && $return_input)
                    {
                        $input = array($return);
                    }
                }
            }
        }
        
        return $return;
    }
}
?>