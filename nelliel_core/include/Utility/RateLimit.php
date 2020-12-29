<?php

namespace Nelliel\Utility;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\NellielPDO;
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

    public function getRecord(string $rate_id)
    {
        $this->loadIfNot($rate_id);
        return $this->records[$rate_id] ?? array();
    }

    public function updateRecord(string $rate_id, array $record)
    {
        $this->records[$rate_id] = $record;
        $this->storeRecord($rate_id);
    }

    private function loadRecord(string $rate_id)
    {
        $prepared = $this->database->prepare(
                'SELECT "record" FROM "' . NEL_RATE_LIMIT_TABLE . '" WHERE "rate_id" = :rate_id');
        $prepared->bindValue(':rate_id', nel_prepare_hash_for_storage($rate_id), PDO::PARAM_LOB);
        $this->records[$rate_id] = unserialize(
                $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN));
    }

    private function storeRecord(string $rate_id)
    {
        $serialized_record = serialize($this->records[$rate_id]);
        $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_RATE_LIMIT_TABLE . '" WHERE "rate_id" = :rate_id');
        $prepared->bindValue(':rate_id', nel_prepare_hash_for_storage($rate_id), PDO::PARAM_LOB);
        $result = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);

        if (!empty($result))
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . NEL_RATE_LIMIT_TABLE . '" SET "record" = :record WHERE "rate_id" = :rate_id');
            $prepared->bindValue(':record', $serialized_record, PDO::PARAM_STR);
            $prepared->bindValue(':rate_id', nel_prepare_hash_for_storage($rate_id), PDO::PARAM_LOB);
            $this->database->executePrepared($prepared);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . NEL_RATE_LIMIT_TABLE . '" (rate_id, record) VALUES (:rate_id, :record)');
            $prepared->bindValue(':rate_id', nel_prepare_hash_for_storage($rate_id), PDO::PARAM_LOB);
            $prepared->bindValue(':record', $serialized_record, PDO::PARAM_STR);
            $this->database->executePrepared($prepared);
        }
    }

    private function loadIfNot(string $rate_id)
    {
        if (!isset($this->records[$rate_id]))
        {
            $this->loadRecord($rate_id);
        }
    }

    public function attempts(string $rate_id, string $key)
    {
        $this->loadIfNot($rate_id);
        return $this->records[$rate_id][$key]['attempts'] ?? 0;
    }

    public function lastTime(string $rate_id, string $key)
    {
        $this->loadIfNot($rate_id);
        return $this->records[$rate_id][$key]['last_attempt'] ?? 0;
    }

    public function updateAttempts(string $rate_id, string $key, bool $store = true)
    {
        if (!isset($this->records[$rate_id][$key]['attempts']))
        {
            $this->records[$rate_id][$key]['attempts'] = 1;
        }
        else
        {
            $this->records[$rate_id][$key]['attempts'] += 1;
        }

        $this->records[$rate_id][$key]['last_attempt'] = time();

        if ($store)
        {
            $this->storeRecord($rate_id);
        }
    }

    public function clearAttempts(string $rate_id, string $key, bool $store = true)
    {
        $this->records[$rate_id][$key]['attempts'] = 0;
        $this->records[$rate_id][$key]['last_attempt'] = time();

        if ($store)
        {
            $this->storeRecord($rate_id);
        }
    }
}
