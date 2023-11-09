<?php
declare(strict_types = 1);

namespace Nelliel\Bans;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use PDO;

class BansAccess
{
    const UNSET = 0;
    const IP = 1;
    const HASHED_IP = 2;
    const RANGE = 3;
    const HASHED_SUBNET = 4;
    const VISITOR_ID = 5;
    private $database;
    private $ban_data = array();

    public function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function getByID(int $ban_id): BanHammer
    {
        return new BanHammer($this->database, $ban_id);
    }

    public function getForIP(string $ban_ip, string $board_id = null): array
    {
        if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ip_address" = ? AND "ban_type" = ' . self::IP .
                ' AND "board_id" = ?');
            $prepared->bindValue(2, $board_id, PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ip_address" = ? AND "ban_type" = ' . self::IP);
        }

        $prepared->bindValue(1, nel_prepare_ip_for_storage($ban_ip, false), PDO::PARAM_LOB);
        $ban_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);

        if (is_array($ban_ids)) {
            return $this->bansToHammers($ban_ids);
        }

        return array();
    }

    public function getForHashedIP(string $hashed_ip, string $board_id = null): array
    {
        if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ban_type" = ' . self::HASHED_IP .
                ' AND "hashed_ip_address" = ? AND "board_id" = ?');
            $prepared->bindValue(2, $board_id, PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ban_type" = ' . self::HASHED_IP .
                ' AND "hashed_ip_address" = ?');
        }

        $prepared->bindValue(1, $hashed_ip, PDO::PARAM_STR);
        $ban_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);

        if (is_array($ban_ids)) {
            return $this->bansToHammers($ban_ids);
        }

        return array();
    }

    public function getForSubnet(string $hashed_subnet, string $board_id = null): array
    {
        if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ban_type" = ' . self::HASHED_SUBNET .
                ' AND "hashed_subnet" = ? AND "board_id" = ?');
            $prepared->bindValue(2, $board_id, PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ban_type" = ' . self::HASHED_SUBNET .
                ' AND "hashed_subnet" = ?');
        }

        $prepared->bindValue(1, $hashed_subnet, PDO::PARAM_STR);
        $ban_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);

        if (is_array($ban_ids)) {
            return $this->bansToHammers($ban_ids);
        }

        return array();
    }

    public function getByType(int $type, string $board_id = null): array
    {
        if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ban_type" = ? AND "board_id" = ?');
            $ban_ids = $this->database->executePreparedFetchAll($prepared, [$type, $board_id], PDO::FETCH_COLUMN);
        } else {
            $prepared = $this->database->prepare('SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ban_type" = ?');
            $ban_ids = $this->database->executePreparedFetchAll($prepared, [$type], PDO::FETCH_COLUMN);
        }

        if (is_array($ban_ids)) {
            return $this->bansToHammers($ban_ids);
        }

        return array();
    }

    public function getBans(string $domain_id): array
    {
        if ($domain_id === Domain::SITE) {
            $ban_ids = array();
        } else if ($domain_id === Domain::GLOBAL) {
            $prepared = $this->database->prepare('SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '"');
            $ban_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);
        } else {
            $prepared = $this->database->prepare('SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "board_id" = ?');
            $ban_ids = $this->database->executePreparedFetchAll($prepared, [$domain_id], PDO::FETCH_COLUMN);
        }

        return $this->bansToHammers($ban_ids);
    }

    private function bansToHammers(array $ban_ids): array
    {
        $ban_hammers = array();

        foreach ($ban_ids as $ban_id) {
            $ban_hammer = new BanHammer($this->database, (int) $ban_id);
            $ban_hammers[] = $ban_hammer;
        }

        return $ban_hammers;
    }
}

