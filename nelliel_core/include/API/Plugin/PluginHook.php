<?php

declare(strict_types=1);

namespace Nelliel\API\Plugin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class PluginHook
{
    private $hook_name;
    private $registered = array();

    function __construct(string $hook_name)
    {
        $this->hook_name = $hook_name;
    }

    public function hookName()
    {
        return $this->hook_name;
    }

    public function addFunction(string $function_name, string $plugin_id, int $priority)
    {
        $this->registered[] = ['type' => 'function', 'function_name' => $function_name, 'plugin_id' => $plugin_id,
            'priority' => $priority];
    }

    public function addMethod($class, string $method_name, string $plugin_id, int $priority)
    {
        $this->registered[] = ['type' => 'method', 'class' => $class, 'method_name' => $method_name,
            'plugin_id' => $plugin_id, 'priority' => $priority];
    }

    public function removeFunction(string $function_name, string $plugin_id)
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

    public function removeMethod($class, string $method_name, string $plugin_id)
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

    public function process(array $args, $returnable)
    {
        usort($this->registered, [$this, 'sortByPriority']);
        $return_type = gettype($returnable);
        array_unshift($args, $returnable);
        $modified = $returnable;

        foreach ($this->registered as $registered)
        {
            if ($registered['type'] === 'function' && function_exists($registered['function_name']))
            {
                $return_value = call_user_func_array($registered['function_name'], $args);
            }
            else if ($registered['type'] === 'method' && method_exists($registered['class'], $registered['method_name']))
            {
                $return_value = call_user_func_array([$registered['class'], $registered['method_name']], $args);
            }

            if (!is_null($return_type) && gettype($return_value) === $return_type)
            {
                $modified = $return_value;
            }

            $args[0] = $modified;
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