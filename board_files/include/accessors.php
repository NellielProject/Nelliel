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
        $authorize = new \Nelliel\Authorization(nel_database());
    }

    return $authorize;
}

function nel_sessions()
{
    static $sessions;

    if (!isset($sessions))
    {
        $sessions = new \Nelliel\Sessions();
    }

    return $sessions;
}

function nel_plugins()
{
    static $plugins;

    if (!isset($plugins))
    {
        $plugins = new \Nelliel\PluginAPI();
    }

    return $plugins;
}

function nel_parameters_and_data()
{
    static $parameters;

    if (!isset($parameters))
    {
        $parameters = new \Nelliel\ParametersAndData();
    }

    return $parameters;
}

function nel_language()
{
    static $language;

    if (!isset($language))
    {
        $language = new \Nelliel\language\Language();
    }

    return $language;
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
