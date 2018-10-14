<?php

namespace ExamplePlugin;

function plugin_example_function($plugin_id)
{
    static $stored_plugin_id;
    $stored_plugin_id = $plugin_id;
    nel_plugins()->addFunction('nel-plugin-example', '\ExamplePlugin\example_function', $stored_plugin_id, 4);
    nel_plugins()->addFunction('nel-plugin-example-return', '\ExamplePlugin\example_function_returnable', $stored_plugin_id, 10);
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
