<?php
plugin_example_function($plugins, FALSE);

function plugin_example_function($plugins, $get_id)
{
    static $plugin_id;
    
    if ($get_id)
    {
        return $plugin_id;
    }
    
    $plugin_id = $plugins->register_plugin(NULL, 'Example plugin using a class', 'Nelliel', 'v1.0');
    $plugins->register_hook_function('plugin-example', array(NULL, 'plugin_example_function_dostuff'), 10, $plugin_id);
}

function plugin_example_function_dostuff($input)
{
    $output = $input + 5;
    return $output;
}
?>