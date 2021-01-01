<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class BansAccess
{
    const UNSET = 0;
    const IP = 1;
    const HASHED_IP = 2;
    const RANGE = 3;
    private $database;
    private $ban_data = array();

    public function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function getBanByID($ban_id)
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_BANS_TABLE . '" WHERE "ban_id" = ?');
        $ban_info = $this->database->executePreparedFetch($prepared, [$ban_id], PDO::FETCH_ASSOC);

        if ($ban_info !== false)
        {
            $ban_info['times'] = $this->secondsToTimeArray($ban_info['length']);
        }
        else
        {
            $ban_info = array();
        }

        return $ban_info;
    }

    public function getBansByIP(string $ban_ip, string $board_id = null)
    {
        if (!is_null($board_id))
        {
            $prepared = $this->database->prepare(
                    'SELECT "ban_id" FROM "' . NEL_BANS_TABLE .
                    '" WHERE "ip_address_start" = ? AND "ip_type" = 1, AND "board_id" = ?');
            $prepared->bindValue(2, $board_id, PDO::PARAM_STR);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ip_address_start" = ? AND "ip_type" = 1');
        }

        $prepared->bindValue(1, nel_prepare_ip_for_storage($ban_ip), PDO::PARAM_LOB);
        $ban_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);

        if (is_array($ban_ids))
        {
            return $this->bansToHammers($ban_ids);
        }

        return array();
    }

    public function getBansByHashedIP(string $hashed_ip, string $board_id = null)
    {
        if (!is_null($board_id))
        {
            $prepared = $this->database->prepare(
                    'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "hashed_ip_address" = ? AND "board_id" = ?');
            $prepared->bindValue(2, $board_id, PDO::PARAM_STR);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "hashed_ip_address" = ?');
        }

        $prepared->bindValue(1, nel_prepare_hash_for_storage($hashed_ip), PDO::PARAM_LOB);
        $ban_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);

        if (is_array($ban_ids))
        {
            return $this->bansToHammers($ban_ids);
        }

        return array();
    }

    public function getBansByType(int $type, string $board_id = null)
    {
        if (!is_null($board_id))
        {
            $prepared = $this->database->prepare(
                    'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ip_type" = ? AND "board_id" = ?');
            $ban_ids = $this->database->executePreparedFetchAll($prepared, [$type, $board_id], PDO::FETCH_COLUMN);
        }
        else
        {
            $prepared = $this->database->prepare('SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ip_type" = ?');
            $ban_ids = $this->database->executePreparedFetchAll($prepared, [$type], PDO::FETCH_COLUMN);
        }

        if (is_array($ban_ids))
        {
            return $this->bansToHammers($ban_ids);
        }

        return array();
    }

    public function getBans(string $board_id = null)
    {
        if (!is_null($board_id))
        {
            $prepared = $this->database->prepare('SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "board_id" = ?');
            $ban_ids = $this->database->executePreparedFetchAll($prepared, [$board_id], PDO::FETCH_COLUMN);
        }
        else
        {
            $prepared = $this->database->prepare('SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '"');
            $ban_ids = $this->database->executePreparedFetchAll($prepared, [], PDO::FETCH_COLUMN);
        }

        if (is_array($ban_ids))
        {
            return $this->bansToHammers($ban_ids);
        }

        return array();
    }

    private function bansToHammers(array $ban_ids)
    {
        $ban_hammers = array();

        foreach ($ban_ids as $ban_id)
        {
            $ban_hammer = new BanHammer($this->database);
            $ban_hammer->loadFromID($ban_id);
            $ban_hammers[] = $ban_hammer;
        }

        return $ban_hammers;
    }
}

