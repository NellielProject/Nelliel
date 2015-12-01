<?php

$plugin_files = glob(PLUGINS_PATH . '*.php');

foreach($plugin_files as $file)
{
    require_once $file;
}

function plugin_hook($hook, $inputs)
{
    global $hooks;

    if(isset($hooks[$hook]))
    {
        foreach($hooks[$hook] as $function)
        {
            if(function_exists($function))
            {
                call_user_func_array($function, $inputs);
            }
        }
    }
}
?>