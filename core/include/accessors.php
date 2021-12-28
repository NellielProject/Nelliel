<?php
defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\DatabaseConnector;
use Nelliel\API\Plugin\PluginAPI;
use Nelliel\Account\Session;
use Nelliel\Domains\DomainGlobal;
use Nelliel\Domains\DomainSite;
use Nelliel\Utility\Utilities;
use Monolog\Logger;
use Nelliel\Logging\NellielLogProcessor;
use Nelliel\Logging\NellielDatabaseHandler;

function nel_database(string $database_key)
{
    static $databases = array();

    if (!array_key_exists($database_key, $databases)) {
        $new_database = new DatabaseConnector($database_key);
        $databases[$database_key] = $new_database->connection();
    }

    return $databases[$database_key];
}

function nel_plugins()
{
    static $plugins;

    if (!isset($plugins)) {
        $plugins = new PluginAPI();
    }

    return $plugins;
}

function nel_site_domain(): DomainSite
{
    static $site_domain;

    if (!isset($site_domain)) {
        $site_domain = new DomainSite(nel_database('core'));
    }

    return $site_domain;
}

function nel_global_domain(): DomainGlobal
{
    static $global_domain;

    if (!isset($global_domain)) {
        $global_domain = new DomainGlobal(nel_database('core'));
    }

    return $global_domain;
}

function nel_request_ip_address(bool $hashed = false)
{
    static $ip_address;
    static $hashed_ip_address;

    if ($hashed) {
        if (!isset($hashed_ip_address)) {
            $hashed_ip_address = nel_ip_hash($_SERVER['REMOTE_ADDR']);
        }

        return $hashed_ip_address;
    } else {
        if (!isset($ip_address)) {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }

        return $ip_address;
    }
}

function nel_utilities()
{
    static $utilities;

    if (!isset($utilities)) {
        $utilities = new Utilities(nel_database('core'));
    }

    return $utilities;
}

function nel_session()
{
    static $session;

    if (!isset($session)) {
        $session = new Session();
    }

    return $session;
}

function nel_logger(string $channel): Logger
{
    static $loggers;

    if (!isset($loggers[$channel])) {
        $logger = new Logger($channel);
        $logger->pushProcessor(new NellielLogProcessor());
        $logger->pushHandler(new NellielDatabaseHandler(nel_database('core')));
        $loggers[$channel] = $logger;
    }

    return $loggers[$channel];
}