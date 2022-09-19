<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Monolog\Logger;
use Nelliel\Database\DatabaseConnector;
use Nelliel\Database\NellielPDO;
use Nelliel\API\Plugin\PluginAPI;
use Nelliel\Account\Session;
use Nelliel\Domains\DomainGlobal;
use Nelliel\Domains\DomainSite;
use Nelliel\Logging\NellielDatabaseHandler;
use Nelliel\Logging\NellielLogProcessor;
use Nelliel\Utility\Utilities;

function nel_database(string $database_key): NellielPDO
{
    static $databases = array();

    if (!array_key_exists($database_key, $databases)) {
        $new_database = new DatabaseConnector($database_key);
        $databases[$database_key] = $new_database->connection();
    }

    return $databases[$database_key];
}

function nel_plugins(): PluginAPI
{
    static $plugins;

    if (!isset($plugins)) {
        $plugins = new PluginAPI(nel_database('core'));
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

function nel_request_ip_address(bool $hashed = false): string
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

function nel_utilities(): Utilities
{
    static $utilities;

    if (!isset($utilities)) {
        $utilities = new Utilities(nel_database('core'));
    }

    return $utilities;
}

function nel_session(): Session
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

function nel_visitor_id(bool $regenerate = false): string
{
    static $visitor_id;

    if ($regenerate) {
        $visitor_id = hash('sha256', (random_bytes(16)));
        setcookie('visitor-id', $visitor_id, time() + nel_site_domain()->setting('visitor_id_lifespan'),
            NEL_BASE_WEB_PATH . '; samesite=strict', '', false, true);
    }

    if (!isset($visitor_id)) {
        $visitor_id = $_COOKIE['visitor-id'] ?? '';
    }

    return $visitor_id;
}