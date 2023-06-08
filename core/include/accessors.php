<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use IPTools\IP;
use IPTools\Network;
use Monolog\Logger;
use Nelliel\API\Plugin\PluginAPI;
use Nelliel\Account\Session;
use Nelliel\Database\DatabaseConnector;
use Nelliel\Database\NellielPDO;
use Nelliel\Domains\DomainGlobal;
use Nelliel\Domains\DomainSite;
use Nelliel\Logging\NellielDatabaseHandler;
use Nelliel\Logging\NellielLogProcessor;
use Nelliel\Utility\Utilities;

function nel_database(string $database_key): NellielPDO
{
    static $database_connections = array();

    if (!array_key_exists($database_key, $database_connections)) {
        $new_connector = new DatabaseConnector();
        $database_connections[$database_key] = $new_connector->getConnection($database_key);
    }

    return $database_connections[$database_key];
}

function nel_plugins(): PluginAPI
{
    static $plugins;

    if (!isset($plugins)) {
        $plugins = new PluginAPI(nel_database('core'));
    }

    return $plugins;
}

function nel_site_domain(bool $renew = false): DomainSite
{
    static $site_domain;

    if (!isset($site_domain) || $renew) {
        $site_domain = new DomainSite(nel_database('core'));
    }

    return $site_domain;
}

function nel_global_domain(bool $renew = false): DomainGlobal
{
    static $global_domain;

    if (!isset($global_domain) || $renew) {
        $global_domain = new DomainGlobal(nel_database('core'));
    }

    return $global_domain;
}

function nel_request_ip_address(bool $hashed = false, bool $single_ip = false): string
{
    static $single_ip_address;
    static $effective_ip_address;
    static $hashed_ip_address;

    if (!isset($single_ip_address)) {
        $single_ip_address = $_SERVER['REMOTE_ADDR'];
    }

    if (!isset($effective_ip_address)) {
        $effective_ip_address = nel_effective_ip($single_ip_address);
    }

    if ($single_ip) {
        return $single_ip_address;
    }

    if ($hashed) {
        if (!isset($hashed_ip_address)) {
            $hashed_ip_address = nel_ip_hash($effective_ip_address);
        }

        return $hashed_ip_address;
    }

    return $effective_ip_address;
}

function nel_effective_ip(string $ip_address): string
{
    $ip = new IP($ip_address);

    if ($ip->getVersion() === IP::IP_V6) {
        $effective_ip_address = Network::parse($ip_address . '/' . nel_site_domain()->setting('ipv6_identification_cidr'))->getCIDR();
    } else {
        $effective_ip_address = $ip_address;
    }

    return $effective_ip_address;
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
        $visitor_id = base64_encode(hash('sha256', (random_bytes(16)), true));
        $visitor_id = 'v-id->' . utf8_substr($visitor_id, 0, 32);
        setcookie('visitor-id', $visitor_id, time() + nel_site_domain()->setting('visitor_id_lifespan'),
            NEL_BASE_WEB_PATH . '; samesite=strict', '', false, true);
    }

    if (!isset($visitor_id)) {
        $visitor_id = $_COOKIE['visitor-id'] ?? '';
    }

    return $visitor_id;
}