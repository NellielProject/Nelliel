<?php

namespace ExamplePlugin;

function plugin_example_function()
{
    static $plugin_id;
    $plugin_id = nel_plugins()->registerPlugin(['name' => 'name']);
    nel_plugins()->addHookFunction('plugin-example', '\ExamplePlugin\example_function', $plugin_id, 4);
    nel_plugins()->addHookFunction('plugin-example-return', '\ExamplePlugin\example_function_returnable', $plugin_id, 10);
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
