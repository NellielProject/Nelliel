<?php
declare(strict_types = 1);

namespace Nelliel\API\Plugin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class PluginHook
{
    private string $name;
    private array $registered = array();
    private bool $in_progress = false;
    private bool $unsorted = false;

    function __construct(string $hook_name)
    {
        $this->name = $hook_name;
    }

    /**
     * Get the hook name.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Checks if the hook is currently being processed.
     */
    public function inProgress(): bool
    {
        return $this->in_progress;
    }

    /**
     * Registers a function to the specified hook.
     */
    public function addFunction(string $function_name, string $plugin_id, int $priority): bool
    {
        if ($this->in_progress) {
            return false;
        }

        if (!is_callable($function_name)) {
            return false;
        }

        $this->registered[] = ['type' => 'function', 'function_name' => $function_name, 'plugin_id' => $plugin_id,
            'priority' => $priority];
        $this->unsorted = true;
        return true;
    }

    /**
     * Registers a method to the specified hook.
     */
    public function addMethod(object $class, string $method_name, string $plugin_id, int $priority): bool
    {
        if ($this->in_progress) {
            return false;
        }

        if (!is_callable([$class, $method_name])) {
            return false;
        }

        $this->registered[] = ['type' => 'method', 'class' => $class, 'method_name' => $method_name,
            'plugin_id' => $plugin_id, 'priority' => $priority];
        $this->unsorted = true;
        return true;
    }

    /**
     * Removes a function from the specified hook.
     */
    public function removeFunction(string $function_name, string $plugin_id, int $priority): bool
    {
        if ($this->in_progress) {
            return false;
        }

        $key = $this->functionKey($function_name, $plugin_id, $priority);

        if (!is_null($key)) {
            unset($this->registered[$key]);
        }

        return true;
    }

    /**
     * Removes a method from the specified hook.
     */
    public function removeMethod($class, string $method_name, string $plugin_id, int $priority): bool
    {
        if ($this->in_progress) {
            return false;
        }

        $key = $this->methodKey($method_name, $class, $plugin_id, $priority);

        if (!is_null($key)) {
            unset($this->registered[$key]);
        }

        return true;
    }

    /**
     * Gets the index of the specified function.
     * Returns null if not found.
     */
    private function functionKey(string $function_name, string $plugin_id, int $priority): ?int
    {
        foreach ($this->registered as $key => $registered) {
            if (!isset($registered['type']) || $registered['type'] !== 'function') {
                continue;
            }

            if ($registered['function_name'] === $function_name && $registered['plugin_id'] === $plugin_id &&
                $registered['priority'] === $priority) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Gets the index of the specified method.
     * Returns null if not found.
     */
    private function methodKey(string $method_name, object $class, string $plugin_id, int $priority): ?int
    {
        foreach ($this->registered as $key => $registered) {
            if (!isset($registered['type']) || $registered['type'] !== 'method') {
                continue;
            }

            if ($registered['method_name'] === $method_name && $registered['class'] === $class &&
                $registered['plugin_id'] === $plugin_id && $registered['priority'] === $priority) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Sorts and processes all registered functions and methods.
     */
    public function process(array $args, $returnable)
    {
        $this->in_progress = true;

        if ($this->unsorted) {
            usort($this->registered, function ($a, $b) {
                return $a['priority'] <=> $b['priority'];
            });
            $this->unsorted = false;
        }

        $return_type = gettype($returnable);
        $has_returnable = !is_null($returnable);

        if ($has_returnable) {
            array_unshift($args, $returnable);
        }

        $modified = $returnable;
        $return_value = null;

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