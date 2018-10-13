<?php

namespace ExamplePlugin;

function plugin_example_function($plugin_id)
{
    static $plugin_id;
    \Nelliel\PluginAPI::registerFunction('plugin-example', '\ExamplePlugin\example_function', $plugin_id, 4);
    \Nelliel\PluginAPI::registerFunction('plugin-example-return', '\ExamplePlugin\example_function_returnable', $plugin_id, 10);
}

function example_function($arg)
{
    if($arg)
    {
        $var = $arg;
    }
}

function example_function_returnable($data, $string)
{
    return $data + 3;
}
