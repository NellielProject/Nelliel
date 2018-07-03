<?php

namespace ExamplePlugin;

class plugin_example_class
{
    private $plugin_id;

    public function __construct()
    {
        $this->plugin_id = nel_plugins()->registerPlugin(['name' => 'name']);
        nel_plugins()->registerMethodForHook('plugin-example', $this, 'event_example', $this->plugin_id, 5);
        nel_plugins()->registerMethodForHook('plugin-example', $this, 'filter_example', $this->plugin_id, 10);
    }

    public function event_example($arg)
    {
        if($arg)
        {
            $var = $arg;
        }
    }

    public function filter_example($data)
    {
        return $data + 3;
    }
}
