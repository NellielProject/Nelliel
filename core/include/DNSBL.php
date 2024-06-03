<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use IPTools\IP;
use Nelliel\Domains\Domain;

class DNSBL
{

    function __construct()
    {}

    public function checkIP(string $ip_address): void
    {
        if (!nel_get_cached_domain(Domain::SITE)->setting('use_dnsbl')) {
            return;
        }

        // TODO: Exclude local IPs here

        $exceptions = json_decode(nel_get_cached_domain(Domain::SITE)->setting('dnsbl_exceptions'));

        if (is_array($exceptions) && in_array($ip_address, $exceptions)) {
            return;
        }

        $ip = new IP($ip_address);

        // We'll add IPv6 support later
        if ($ip->getOctetsCount() > 4) {
            return;
        }

        // IPTools appends these for the pointer but we don't need them
        $reverse_ip = utf8_str_replace(['.in-addr.arpa', '.ip6.arpa'], '', $ip->getReversePointer());
        $services = $this->getServices();

        foreach ($services as $service) {
            if (!isset($service[0])) {
                continue;
            }

            $service_domain = $service[0];

            // For special cases like Http:BL (http://www.projecthoneypot.org/httpbl.php)
            $service_domain = utf8_str_replace('%', $ip->__toString(), $service_domain);

            $lookup_result = $this->lookup($reverse_ip . '.' . $service_domain);

            if (empty($lookup_result)) {
                continue;
            }

            $return_handler = $service[1] ?? null;

            foreach ($lookup_result as $entry) {
                if (!isset($entry['ip'])) {
                    continue;
                }

                // Match any result when no handler specified
                $bad_ip = is_null($return_handler);

                $return_ip = $entry['ip'];
                $return_octets = explode('.', $return_ip);
                $end_octet = intval(end($return_octets));

                // Match to single IP or octet
                if (!$bad_ip && (is_int($return_handler) || is_string($return_handler))) {
                    $bad_ip = ($return_ip === $return_handler || $return_handler == $end_octet);
                }

                // Match in array of IPs or octets
                if (!$bad_ip && is_array($return_handler)) {
                    $bad_ip = in_array($return_ip, $return_handler) || in_array($end_octet, $return_handler);
                }

                // Match if handler function returns true
                if (!$bad_ip && is_callable($return_handler)) {
                    $bad_ip = $return_handler($return_ip);
                }

                if ($bad_ip) {
                    nel_derp(157, sprintf(_gettext('Your IP was found on a DNS blacklist: %s'), $service_domain));
                }
            }
        }
    }

    public function lookup(string $lookup_address): array
    {
        $lookup_result = dns_get_record($lookup_address, DNS_A);
        return (is_array($lookup_result)) ? $lookup_result : array();
    }

    protected function getServices(): array
    {
        $services = array();

        if (file_exists(NEL_CONFIG_FILES_PATH . 'dnsbl.php')) {
            include NEL_CONFIG_FILES_PATH . 'dnsbl.php';
        }

        return $services;
    }
}
