<?php

namespace ExamplePlugin;

function plugin_example_function()
{
    static $plugin_id;
    $plugin_id = nel_plugins()->registerPlugin(['name' => 'name']);
    nel_plugins()->registerFunctionForHook('plugin-example', '\ExamplePlugin\plugin_example_function_dostuff', $plugin_id, 4);
}

function plugin_example_function_dostuff($input)
{
    $output = $input + 3;
    return $output;
}
