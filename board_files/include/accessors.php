<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_plugins()
{
    static $plugins;

    if (!isset($plugins))
    {
        $plugins = new \Nelliel\API\PluginAPI();
    }

    return $plugins;
}

function nel_parameters_and_data()
{
    static $parameters;

    if (!isset($parameters))
    {
        $parameters = new \Nelliel\ParametersAndData(nel_database(), new \Nelliel\CacheHandler(true));
    }

    return $parameters;
}
