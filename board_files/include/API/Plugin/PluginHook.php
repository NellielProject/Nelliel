<?php

namespace Nelliel\API\Plugin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class PluginHook
{
    private $hook_name;
    private $registered = array();

    function __construct($hook_name)
    {
        $this->hook_name = $hook_name;
    }

    public function hookName()
    {
        return $this->hook_name;
    }

    public function addFunction($function_name, $plugin_id, $priority)
    {
        $this->registered[] = ['type' => 'function', 'function_name' => $function_name, 'plugin_id' => $plugin_id,
            'priority' => $priority];
    }

    public function addMethod($class, $method_name, $plugin_id, $priority)
    {
        $this->registered[] = ['type' => 'method', 'class' => $class, 'method_name' => $method_name,
            'plugin_id' => $plugin_id, 'priority' => $priority];
    }

    public function removeFunction($function_name, $plugin_id)
    {
        foreach ($this->registered as $key => $registered)
        {
            if (isset($registered['function_name']) && $registered['function_name'] === $function_name &&
                    $registered['plugin_id'] === $plugin_id)
            {
                unset($this->registered[$key]);
                return true;
            }
        }

        return false;
    }

    public function removeMethod($class, $method_name, $plugin_id)
    {
        foreach ($this->registered as $key => $registered)
        {
            if (isset($registered['method_name']) && $registered['method_name'] === $method_name &&
                    $registered['class'] === $class && $registered['plugin_id'] === $plugin_id)
            {
                unset($this->registered[$key]);
                return true;
            }
        }

        return false;
    }

    public function process($args, $returnable)
    {
        usort($this->registered, array($this, 'sortByPriority'));

        if (!is_array($args))
        {
            $args = [0 => $args];
        }

        $arguments_array = $args;
        $needs_return = !is_null($returnable);
        $return_type = gettype($returnable);

        if ($needs_return)
        {
            array_unshift($arguments_array, $returnable);
        }

        $modified = $returnable;

        foreach ($this->registered as $registered)
        {
            if ($registered['type'] === 'function' && function_exists($registered['function_name']))
            {
                $return = call_user_func_array($registered['function_name'], $arguments_array);
            }
            else if ($registered['type'] === 'method' && method_exists($registered['class'], $registered['method_name']))
            {
                $return = call_user_func_array([$registered['class'], $registered['method_name']], $arguments_array);
            }

            if ($needs_return && gettype($return) === $return_type)
            {
                $modified = $return;
                $arguments_array[0] = $modified;
            }
        }

        return $modified;
    }

    private function sortByPriority($a, $b)
    {
        if ($a['priority'] == $b['priority'])
        {
            return $a['priority'] - $b['priority'];
        }

        return ($a['priority'] < $b['priority']) ? -1 : 1;
    }
}