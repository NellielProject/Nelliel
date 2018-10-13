<?php

namespace ExamplePlugin;

class plugin_example_class
{
    private $plugin_id;

    public function __construct($plugin_id)
    {
        $this->plugin_id = $plugin_id;
        \Nelliel\PluginAPI::registerMethod('plugin-example', $this, 'example_method', $this->plugin_id, 5);
        \Nelliel\PluginAPI::registerMethod('plugin-example-return', $this, 'example_method_returnable', $this->plugin_id, 10);
    }

    public function example_method($arg)
    {
        if($arg)
        {
            $var = $arg;
        }
    }

    public function example_method_returnable($data, $string)
    {
        return $data + 3;
    }
}
