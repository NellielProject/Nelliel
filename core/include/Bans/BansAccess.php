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
    private NellielPDO $database;
    private array $ban_data = array();

    public function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function getByID(int $ban_id): BanHammer
    {
        return new BanHammer($this->database, $ban_id);
    }

    public function getForIP(string $ban_ip, string $board_id = null, int $page = 0, int $entries = 0): array
    {
        $limit_clause = ($entries > 0) ? ' LIMIT :limit OFFSET :offset' : '';

        if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE .
                '" WHERE "unhashed_ip_address" = :unhashed_ip_address AND "ban_type" = ' . self::IP .
                ' AND "board_id" = :board_id' . $limit_clause);
            $prepared->bindValue(':board_id', $board_id, PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE .
                '" WHERE "unhashed_ip_address" = :unhashed_ip_address AND "ban_type" = ' . self::IP . $limit_clause);
        }

        $prepared->bindValue(':unhashed_ip_address', $ban_ip, PDO::PARAM_STR);

        if ($entries > 0) {
            $prepared->bindValue(':limit', $entries, PDO::PARAM_INT);
            $prepared->bindValue(':offset', nel_utilities()->sqlHelpers()->paginationOffset($page, $entries),
                PDO::PARAM_INT);
        }

        $ban_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);
        return $this->bansToHammers($ban_ids);
    }

    public function getCountForIP(string $ban_ip, string $board_id = null): int
    {
        if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT COUNT(*) FROM "' . NEL_BANS_TABLE .
                '" WHERE "unhashed_ip_address" = :unhashed_ip_address AND "ban_type" = \'' . self::IP .
                '\' AND "board_id" = :board_id');
            $prepared->bindValue(':board_id', $board_id, PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare(
                'SELECT COUNT(*) FROM "' . NEL_BANS_TABLE .
                '" WHERE "unhashed_ip_address" = :unhashed_ip_address AND "ban_type" = \'' . self::IP . '\'');
        }

        $prepared->bindValue(':unhashed_ip_address', $ban_ip, PDO::PARAM_STR);
        return (int) $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
    }

    public function getForHashedIP(string $hashed_ip, string $board_id = null, int $page = 0, int $entries = 0): array
    {
        $limit_clause = ($entries > 0) ? ' LIMIT :limit OFFSET :offset' : '';

        if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ban_type" = ' . self::HASHED_IP .
                ' AND "hashed_ip_address" = :hashed_ip_address AND "board_id" = :board_id' . $limit_clause);
            $prepared->bindValue(':board_id', $board_id, PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ban_type" = ' . self::HASHED_IP .
                ' AND "hashed_ip_address" = :hashed_ip_address' . $limit_clause);
        }

        $prepared->bindValue(':hashed_ip_address', $hashed_ip, PDO::PARAM_STR);

        if ($entries > 0) {
            $prepared->bindValue(':limit', $entries, PDO::PARAM_INT);
            $prepared->bindValue(':offset', nel_utilities()->sqlHelpers()->paginationOffset($page, $entries),
                PDO::PARAM_INT);
        }

        $ban_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);
        return $this->bansToHammers($ban_ids);
    }

    public function getCountForHashedIP(string $hashed_ip, string $board_id = null): int
    {
        if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT COUNT(*) FROM "' . NEL_BANS_TABLE .
                '" WHERE "hashed_ip_address" = :hashed_ip_address AND "ban_type" = \'' . self::HASHED_IP .
                '\' AND "board_id" = :board_id');
            $prepared->bindValue(':board_id', $board_id, PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare(
                'SELECT COUNT(*) FROM "' . NEL_BANS_TABLE .
                '" WHERE "hashed_ip_address" = :hashed_ip_address AND "ban_type" = \'' . self::HASHED_IP . '\'');
        }

        $prepared->bindValue(':hashed_ip_address', $hashed_ip, PDO::PARAM_STR);
        return (int) $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
    }

    public function getForSubnet(string $hashed_subnet, string $board_id = null, int $page = 0, int $entries = 0): array
    {
        $limit_clause = ($entries > 0) ? ' LIMIT :limit OFFSET :offset' : '';

        if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ban_type" = ' . self::HASHED_SUBNET .
                ' AND "hashed_subnet" = :hashed_subnet AND "board_id" = :board_id' . $limit_clause);
            $prepared->bindValue(':board_id', $board_id, PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ban_type" = ' . self::HASHED_SUBNET .
                ' AND "hashed_subnet" = :hashed_subnet' . $limit_clause);
        }

        $prepared->bindValue(':hashed_subnet', $hashed_subnet, PDO::PARAM_STR);

        if ($entries > 0) {
            $prepared->bindValue(':limit', $entries, PDO::PARAM_INT);
            $prepared->bindValue(':offset', nel_utilities()->sqlHelpers()->paginationOffset($page, $entries),
                PDO::PARAM_INT);
        }

        $ban_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);
        return $this->bansToHammers($ban_ids);
    }

    public function getCountForSubnet(string $hashed_subnet, string $board_id = null): int
    {
        if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT COUNT(*) FROM "' . NEL_BANS_TABLE .
                '" WHERE "hashed_subnet" = :hashed_subnet AND "ban_type" = \'' . self::HASHED_SUBNET .
                '\' AND "board_id" = :board_id');
            $prepared->bindValue(':board_id', $board_id, PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare(
                'SELECT COUNT(*) FROM "' . NEL_BANS_TABLE .
                '" WHERE "hashed_subnet" = :hashed_subnet AND "ban_type" = \'' . self::HASHED_SUBNET . '\'');
        }

        $prepared->bindValue(':hashed_subnet', $hashed_subnet, PDO::PARAM_STR);
        return (int) $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
    }

    public function getByType(int $type, string $board_id = null, int $page = 0, int $entries = 0): array
    {
        $limit_clause = ($entries > 0) ? ' LIMIT :limit OFFSET :offset' : '';

        if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ban_type" = :ban_type AND "board_id" = :board_id' .
                $limit_clause);
            $prepared->bindValue(':board_id', $board_id, PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ban_type" = :ban_type' . $limit_clause);
        }

        $prepared->bindValue(':ban_type', $type, PDO::PARAM_STR);

        if ($entries > 0) {
            $prepared->bindValue(':limit', $entries, PDO::PARAM_INT);
            $prepared->bindValue(':offset', nel_utilities()->sqlHelpers()->paginationOffset($page, $entries),
                PDO::PARAM_INT);
        }

        $ban_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);
        return $this->bansToHammers($ban_ids);
    }

    public function getCountForType(int $type, string $board_id = null): int
    {
        if (!is_null($board_id)) {
            $prepared = $this->database->prepare(
                'SELECT COUNT(*) FROM "' . NEL_BANS_TABLE . '" WHERE "ban_type" = :ban_type AND "board_id" = :board_id');
            $prepared->bindValue(':board_id', $board_id, PDO::PARAM_STR);
        } else {
            $prepared = $this->database->prepare('SELECT COUNT(*) FROM "' . NEL_BANS_TABLE . '" "ban_type" = :ban_type');
        }

        $prepared->bindValue(':ban_type', $type, PDO::PARAM_STR);
        return (int) $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
    }

    public function getBans(string $domain_id, int $page = 0, int $entries = 0): array
    {
        $limit_clause = ($entries > 0) ? ' LIMIT :limit OFFSET :offset' : '';

        if ($domain_id === Domain::SITE) {
            $ban_ids = array();
        } else if ($domain_id === Domain::GLOBAL) {
            $prepared = $this->database->prepare('SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '"' . $limit_clause);
        } else {
            $prepared = $this->database->prepare(
                'SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "board_id" = :board_id' . $limit_clause);
            $prepared->bindValue(':board_id', $domain_id, PDO::PARAM_STR);
        }

        if ($entries > 0) {
            $prepared->bindValue(':limit', $entries, PDO::PARAM_INT);
            $prepared->bindValue(':offset', nel_utilities()->sqlHelpers()->paginationOffset($page, $entries),
                PDO::PARAM_INT);
        }

        $ban_ids = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_COLUMN);
        return $this->bansToHammers($ban_ids);
    }

    public function getCountForDomain(string $domain_id): int
    {
        $prepared = $this->database->prepare(
            'SELECT COUNT(*) FROM "' . NEL_BANS_TABLE . '" WHERE "board_id" = :board_id');
        $prepared->bindValue(':board_id', $domain_id, PDO::PARAM_STR);
        return (int) $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
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

