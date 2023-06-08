<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use IPTools\Network;
use PDO;
use IPTools\IP;

class IPInfo
{
    private $hashed_ip_address;
    private $ip_address;
    private $info = array();
    private $database;

    function __construct(string $hashed_ip_address, string $ip_address = null)
    {
        $this->database = nel_site_domain()->database();
        $this->hashed_ip_address = $hashed_ip_address;

        if (!$this->hashInDatabase() && !is_null($ip_address)) {
            $this->updateIP($ip_address);
        }

        $this->load();
    }

    private function load(): void
    {
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_IP_INFO_TABLE . '" WHERE "hashed_ip_address" = :hashed_ip_address');
        $prepared->bindValue(':hashed_ip_address', $this->hashed_ip_address, PDO::PARAM_STR);
        $result = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if ($result !== false) {
            $this->info = $result;
            $this->ip_address = $this->getInfo('ip_address');
        }
    }

    private function store(): void
    {
        if ($this->hashInDatabase()) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_IP_INFO_TABLE .
                '" SET "hashed_ip_address" = :hashed_ip_address, "ip_address" = :ip_address, "hashed_small_subnet" = :hashed_small_subnet, "hashed_large_subnet" = :hashed_large_subnet, "last_activity" = :last_activity WHERE "hashed_ip_address" = :hashed_ip_address');
        } else if ($this->IPInDatabase()) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_IP_INFO_TABLE .
                '" SET "hashed_ip_address" = :hashed_ip_address, "ip_address" = :ip_address, "hashed_small_subnet" = :hashed_small_subnet, "hashed_large_subnet" = :hashed_large_subnet, "last_activity" = :last_activity WHERE "ip_address" = :ip_address');
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_IP_INFO_TABLE .
                '" ("hashed_ip_address", "ip_address", "hashed_small_subnet", "hashed_large_subnet", "last_activity") VALUES (:hashed_ip_address, :ip_address, :hashed_small_subnet, :hashed_large_subnet, :last_activity)');
        }

        $prepared->bindValue(':ip_address', nel_prepare_ip_for_storage($this->ip_address), PDO::PARAM_LOB);
        $prepared->bindValue(':hashed_ip_address', $this->hashed_ip_address, PDO::PARAM_STR);
        $prepared->bindValue(':hashed_small_subnet', $this->info['hashed_small_subnet'] ?? null, PDO::PARAM_STR);
        $prepared->bindValue(':hashed_large_subnet', $this->info['hashed_large_subnet'] ?? null, PDO::PARAM_STR);
        $prepared->bindValue(':last_activity', $this->info['last_activity'] ?? time(), PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    private function hashInDatabase(): bool
    {
        if (!empty($this->hashed_ip_address)) {
            return $this->database->rowExists(NEL_IP_INFO_TABLE, ['hashed_ip_address'], [$this->hashed_ip_address],
                [PDO::PARAM_STR]);
        }

        return false;
    }

    private function IPInDatabase(): bool
    {
        if (!empty($this->ip_address)) {
            return $this->database->rowExists(NEL_IP_INFO_TABLE, ['ip_address'],
                [nel_prepare_ip_for_storage($this->ip_address)], [PDO::PARAM_LOB]);
        }

        return false;
    }

    public function updateIP(string $new_ip_address): void
    {
        $this->info['ip_address'] = $new_ip_address;
        $this->ip_address = $new_ip_address;
        $this->hashed_ip_address = nel_ip_hash($new_ip_address, true);
        $ip = new IP($new_ip_address);

        if ($ip->getVersion() === IP::IP_V6) {
            $small_network = Network::parse(
                $new_ip_address . '/' . nel_site_domain()->setting('ipv6_small_subnet_cidr'));
            $large_network = Network::parse(
                $new_ip_address . '/' . nel_site_domain()->setting('ipv6_large_subnet_cidr'));
        } else {
            $small_network = Network::parse(
                $new_ip_address . '/' . nel_site_domain()->setting('ipv4_small_subnet_cidr'));
            $large_network = Network::parse(
                $new_ip_address . '/' . nel_site_domain()->setting('ipv4_large_subnet_cidr'));
        }

        $this->info['hashed_small_subnet'] = nel_ip_hash($small_network->getCIDR(), true);
        $this->info['hashed_large_subnet'] = nel_ip_hash($large_network->getCIDR(), true);
        $this->store();
    }

    public function updateLastActivity(int $time): void
    {
        $this->info['last_activity'] = $time;
        $this->store();
    }

    public function getInfo(string $key)
    {
        return $this->info[$key] ?? null;
    }
}