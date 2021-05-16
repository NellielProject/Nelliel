<?php
declare(strict_types = 1);

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use IPTools\IP;
use PDO;

class DNSBL
{
    protected $database;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function checkIP(string $ip_address): void
    {
        if(!nel_site_domain()->setting('use_dnsbl'))
        {
            return;
        }

        $exceptions = json_decode(nel_site_domain()->setting('dnsbl_exceptions'));

        if(is_array($exceptions) && in_array($ip_address, $exceptions))
        {
            return;
        }

        $ip = new IP($ip_address);
        // IPTools appends these for the pointer but we don't need them
        $reverse_ip = str_replace(['.in-addr.arpa', '.ip6.arpa'], '', $ip->getReversePointer());
        $services = $this->getServices();

        foreach ($services as $service)
        {
            $lookup_result = $this->lookup($reverse_ip . '.' . $service['service_url']);
            $return_codes = json_decode($service['return_codes'], true);

            if (empty($lookup_result) || !is_array($return_codes))
            {
                continue;
            }

            foreach ($lookup_result as $entry)
            {
                if (!isset($entry['ip']))
                {
                    continue;
                }

                if (in_array($entry['ip'], $return_codes['block']))
                {
                    nel_derp(157, sprintf(_gettext('Your IP was found on a DNS blacklist: %s'), $service['service_url']));
                }
            }
        }
    }

    public function lookup(string $lookup_address): array
    {
        $lookup_result = dns_get_record($lookup_address);
        return (is_array($lookup_result)) ? $lookup_result : array();
    }

    protected function getServices(): array
    {
        $services = array();
        $query = 'SELECT * FROM "' . NEL_DNSBL_TABLE . '" WHERE "enabled" = 1';
        $result = $this->database->executeFetchAll($query, PDO::FETCH_ASSOC);

        if (is_array($result))
        {
            $services = $result;
        }

        return $services;
    }
}
