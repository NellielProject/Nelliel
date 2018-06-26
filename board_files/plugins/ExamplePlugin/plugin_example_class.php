<?php

namespace ExamplePlugin;

class plugin_example_class
{
    private $plugin_id;

    public function __construct()
    {
        $this->plugin_id = nel_plugins()->registerPlugin(['name' => 'name']);
        nel_plugins()->registerMethodForHook('plugin-example', $this, 'plugin_example', $this->plugin_id, 5);
    }

    public function plugin_example($input)
    {
        $output = $input + 5;
        var_dump($output);
        return $output;
    }
}
