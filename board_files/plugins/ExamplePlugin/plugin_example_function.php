<?php

namespace ExamplePlugin;

function plugin_example_function()
{
    static $plugin_id;
    $plugin_id = nel_plugins()->registerPlugin(['name' => 'name']);
    nel_plugins()->registerFunctionForHook('plugin-example', '\ExamplePlugin\plugin_example_function_event', $plugin_id, 4);
    nel_plugins()->registerFunctionForHook('plugin-example', '\ExamplePlugin\plugin_example_function_filter', $plugin_id, 10);
}

function plugin_example_function_event($arg)
{
    if($arg)
    {
        $var = $arg;
    }
}

function plugin_example_function_filter($data)
{
    return $data + 3;
}
