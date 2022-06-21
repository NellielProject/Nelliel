<?php
declare(strict_types = 1);

namespace Nelliel\API\Plugin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class PluginHook
{
    private $name;
    private $registered = array();
    private $in_progress = false;
    private $unsorted = true;

    function __construct(string $hook_name)
    {
        $this->name = $hook_name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function inProgress(): bool
    {
        return $this->in_progress;
    }

    public function addFunction(string $function_name, string $plugin_id, int $priority): bool
    {
        if ($this->in_progress) {
            return false;
        }

        $this->registered[] = ['type' => 'function', 'function_name' => $function_name, 'plugin_id' => $plugin_id,
            'priority' => $priority];
        $this->unsorted = true;
        return true;
    }

    public function addMethod($class, string $method_name, string $plugin_id, int $priority): bool
    {
        if ($this->in_progress) {
            return false;
        }

        $this->registered[] = ['type' => 'method', 'class' => $class, 'method_name' => $method_name,
            'plugin_id' => $plugin_id, 'priority' => $priority];
        $this->unsorted = true;
        return true;
    }

    public function removeFunction(string $function_name, string $plugin_id, int $priority): bool
    {
        if ($this->in_progress) {
            return false;
        }

        foreach ($this->registered as $key => $registered) {
            if (isset($registered['function_name']) && $registered['function_name'] === $function_name &&
                $registered['plugin_id'] === $plugin_id && $registered['priority'] === $priority) {
                unset($this->registered[$key]);
                return true;
            }
        }

        return false;
    }

    public function removeMethod($class, string $method_name, string $plugin_id, int $priority): bool
    {
        if ($this->in_progress) {
            return false;
        }

        foreach ($this->registered as $key => $registered) {
            if (isset($registered['method_name']) && $registered['method_name'] === $method_name &&
                $registered['class'] === $class && $registered['plugin_id'] === $plugin_id &&
                $registered['priority'] === $priority) {
                unset($this->registered[$key]);
                return true;
            }
        }

        return false;
    }

    public function process(array $args, $returnable)
    {
        $this->in_progress = true;
        usort($this->registered, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        $return_type = gettype($returnable);
        $has_returnable = !is_null($returnable);

        if ($has_returnable) {
            array_unshift($args, $returnable);
        }

        $modified = $returnable;

        foreach ($this->registered as $registered) {
            if ($registered['type'] === 'function' && function_exists($registered['function_name'])) {
                $return_value = call_user_func_array($registered['function_name'], $args);
            } else if ($registered['type'] === 'method' &&
                method_exists($registered['class'], $registered['method_name'])) {
                $return_value = call_user_func_array([$registered['class'], $registered['method_name']], $args);
            }

            if ($has_returnable && gettype($return_value) === $return_type) {
                $modified = $return_value;
                $args[0] = $modified;
            }
        }

        $this->in_progress = false;
        return $modified;
    }
}