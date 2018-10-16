<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_authorize()
{
    static $authorize;

    if (!isset($authorize))
    {
        $authorize = new \Nelliel\Auth\Authorization(nel_database());
    }

    return $authorize;
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

function nel_fgsfds($entry, $new_value = null)
{
    static $fgsfds;

    if (!isset($fgsfds))
    {
        $fgsfds = array();
    }

    if (!is_null($new_value))
    {
        $fgsfds[$entry] = $new_value;
    }

    if (isset($fgsfds[$entry]))
    {
        return $fgsfds[$entry];
    }

    return null;
}
