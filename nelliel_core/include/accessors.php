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
        $plugins = new \Nelliel\API\Plugin\PluginAPI();
    }

    return $plugins;
}
