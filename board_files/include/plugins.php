<?php

$plugin_files = glob(PLUGINS_PATH . '*.php');

foreach($plugin_files as $file)
{
    require_once $file;
}

function plugin_hook($hook, $pass_input_array, $input)
{
    global $hooks;

    if(isset($hooks[$hook]))
    {
        foreach($hooks[$hook] as $function)
        {
            if(function_exists($function))
            {
                if($pass_input_array)
                {
                    $return = call_user_func($function, $input);
                }
                else
                {
                    $return = call_user_func_array($function, $input);
                }
            }
        }
    }
    
    return $return;
}
?>