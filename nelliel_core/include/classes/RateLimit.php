<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class RateLimit
{
    private $database;
    private $records;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
        $this->records = array();
    }

    public function getRecord(string $ip_address)
    {
        $this->loadIfNot($ip_address);
        return $this->records[$ip_address] ?? array();
    }

    public function updateRecord(string $ip_address, array $record)
    {
        $this->records[$ip_address] = $record;
        $this->storeRecord($ip_address);
    }

    private function loadRecord(string $ip_address)
    {
        $prepared = $this->database->prepare(
                'SELECT "record" FROM "' . RATE_LIMIT_TABLE . '" WHERE "ip_address" = :ip_address');
        $prepared->bindValue(':ip_address', @inet_pton($ip_address), PDO::PARAM_LOB);
        $this->records[$ip_address] = unserialize(
                $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN));
    }

    private function storeRecord(string $ip_address)
    {
        $serialized_record = serialize($this->records[$ip_address]);
        $prepared = $this->database->prepare(
                'SELECT 1 FROM "' . RATE_LIMIT_TABLE . '" WHERE "ip_address" = :ip_address');
        $prepared->bindValue(':ip_address', @inet_pton($ip_address), PDO::PARAM_LOB);
        $result = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

        if (!empty($result))
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . RATE_LIMIT_TABLE . '" SET "record" = :record WHERE "ip_address" = :ip_address');
            $prepared->bindValue(':record', $serialized_record, PDO::PARAM_STR);
            $prepared->bindValue(':ip_address', @inet_pton($ip_address), PDO::PARAM_LOB);
            $this->database->executePrepared($prepared);
        }
        else
        {
            $rate_key = hash('sha256', random_bytes(16));
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . RATE_LIMIT_TABLE .
                    '" (rate_key, ip_address, record) VALUES (:rate_key, :ip_address, :record)');
            $prepared->bindValue(':rate_key', $rate_key, PDO::PARAM_STR);
            $prepared->bindValue(':ip_address', @inet_pton($ip_address), PDO::PARAM_LOB);
            $prepared->bindValue(':record', $serialized_record, PDO::PARAM_STR);
            $this->database->executePrepared($prepared);
        }
    }

    private function loadIfNot(string $ip_address)
    {
        if (!isset($this->records[$ip_address]))
        {
            $this->loadRecord($ip_address);
        }
    }

    public function attempts(string $ip_address, string $key)
    {
        $this->loadIfNot($ip_address);
        return $this->records[$ip_address][$key]['attempts'] ?? 0;
    }

    public function lastTime(string $ip_address, string $key)
    {
        $this->loadIfNot($ip_address);
        return $this->records[$ip_address][$key]['last_attempt'] ?? 0;
    }

    public function updateAttempts(string $ip_address, string $key, bool $store = true)
    {
        if (!isset($this->records[$ip_address][$key]['attempts']))
        {
            $this->records[$ip_address][$key]['attempts'] = 1;
        }
        else
        {
            $this->records[$ip_address][$key]['attempts'] += 1;
        }

        $this->records[$ip_address][$key]['last_attempt'] = time();

        if ($store)
        {
            $this->storeRecord($ip_address);
        }
    }

    public function clearAttempts(string $ip_address, string $key, bool $store = true)
    {
        $this->records[$ip_address][$key]['attempts'] = 0;
        $this->records[$ip_address][$key]['last_attempt'] = time();

        if ($store)
        {
            $this->storeRecord($ip_address);
        }
    }
}
