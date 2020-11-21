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

function nel_site_domain()
{
    static $site_domain;

    if (!isset($site_domain))
    {
        $site_domain = new \Nelliel\DomainSite(nel_database());
    }

    return $site_domain;
}

function nel_request_ip_address(bool $hashed = false)
{
    static $ip_address;
    static $hashed_ip_address;

    if ($hashed)
    {
        if(!isset($hashed_ip_address))
        {
            $hashed_ip_address = hash('sha256', $_SERVER['REMOTE_ADDR']);
        }

        return $hashed_ip_address;
    }
    else
    {
        if (!isset($ip_address))
        {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }

        return $ip_address;
    }
}