<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class BanHammer
{
    private $database;
    private $ban_data = array();

    public function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function getData(string $key)
    {
        return $this->ban_data[$key] ?? null;
    }

    public function modifyData(string $key, $data)
    {
        $this->ban_data[$key] = $data;
    }

    public function collectFromPOST()
    {
        $this->ban_data['ban_id'] = $_POST['ban_id'] ?? null;
        $this->ban_data['board'] = $_POST['ban_board'] ?? null;

        if (isset($_POST['ban_all_boards']) && is_array($_POST['ban_all_boards']))
        {
            $this->ban_data['all_boards'] = nel_form_input_default($_POST['ban_all_boards']);

            if ($this->ban_data['all_boards'] > 0)
            {
                $this->ban_data['board'] = null;
            }
        }
        else
        {
            $this->ban_data['all_boards'] = 0;
        }

        $this->ban_data['ban_type'] = $_POST['ban_type'] ?? null;
        $this->ban_data['creator'] = $_SESSION['user_id'] ?? null;
        $this->ban_data['ip_address_start'] = $_POST['ban_ip_start'] ?? null;
        $this->ban_data['ip_address_end'] = $_POST['ban_ip_end'] ?? null;
        $this->ban_data['hashed_ip_address'] = $_POST['ban_hashed_ip'] ?? null;
        $address_type = $_POST['address_type'] ?? null;

        if (!is_null($address_type))
        {
            switch ($address_type)
            {
                case 'single':
                    $this->ban_data['ip_address_end'] = null;
                    $this->ban_data['hashed_ip_address'] = hash('sha256', $this->ban_data['ip_address_start']);
                    $this->ban_data['ip_type'] = BansAccess::IP;
                    break;

                case 'range':
                    $this->ban_data['hashed_ip_address'] = null;
                    $this->ban_data['ip_type'] = BansAccess::RANGE;
                    break;

                case 'hash':
                    $this->ban_data['ip_address_start'] = null;
                    $this->ban_data['ip_address_end'] = null;
                    $this->ban_data['ip_type'] = BansAccess::HASHED_IP;
                    break;

                default:
                    $this->ban_data['ip_type'] = BansAccess::UNSET;
            }
        }

        $this->ban_data['times']['years'] = $_POST['ban_time_years'] ?? 0;
        $this->ban_data['times']['months'] = $_POST['ban_time_months'] ?? 0;
        $this->ban_data['times']['days'] = $_POST['ban_time_days'] ?? 0;
        $this->ban_data['times']['hours'] = $_POST['ban_time_hours'] ?? 0;
        $this->ban_data['times']['minutes'] = $_POST['ban_time_minutes'] ?? 0;
        $this->ban_data['times']['seconds'] = $_POST['ban_time_seconds'] ?? 0;
        $this->ban_data['start_time'] = $_POST['ban_start_time'] ?? time();
        $this->ban_data['reason'] = $_POST['ban_reason'] ?? null;
        $this->ban_data['appeal'] = $_POST['ban_appeal'] ?? null;
        $this->ban_data['appeal_response'] = $_POST['ban_appeal_response'] ?? null;
        $this->ban_data['appeal_status'] = $_POST['ban_appeal_status'] ?? 0;
        $this->ban_data['length'] = $this->timeArrayToSeconds($this->ban_data['times']);
        $this->ban_data['ban_hash'] = $_POST['ban_hash'] ?? hash('sha256', random_bytes(8));
    }

    private function secondsToTimeArray($seconds)
    {
        $time = array();
        $time['years'] = floor($seconds / 31536000);
        $time['months'] = floor(($seconds % 31536000) / 2592000);
        $time['days'] = floor((($seconds % 31536000) % 2592000) / 86400);
        $time['hours'] = floor(((($seconds % 31536000) % 2592000) % 86400) / 3600);
        $time['minutes'] = floor((((($seconds % 31536000) % 2592000) % 86400) % 3600) / 60);
        $time['seconds'] = $seconds % 60;
        return $time;
    }

    private function timeArrayToSeconds(array $time)
    {
        return ($time['years'] * 31536000) + ($time['months'] * 2592000) + ($time['days'] * 86400) +
                ($time['hours'] * 3600) + ($time['minutes'] * 60) + $time['seconds'];
    }

    public function timeToExpiration()
    {
        $start = $this->getData('start_time') ?? 0;
        $length = $this->getData('length') ?? 0;
        $expiration = $start + $length;
        return $expiration - time();
    }

    public function expired()
    {
        return $this->timeToExpiration() <= 0;
    }

    public function loadFromID($ban_id)
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_BANS_TABLE . '" WHERE "ban_id" = ?');
        $ban_data = $this->database->executePreparedFetch($prepared, [$ban_id], PDO::FETCH_ASSOC);

        if ($ban_data !== false)
        {
            $ban_data['ip_type'] = intval($ban_data['ip_type']);
            $ban_data['ban_hash'] = nel_convert_hash_from_storage($ban_data['ban_hash']);
            $ban_data['ip_address_start'] = nel_convert_ip_from_storage($ban_data['ip_address_start']);
            $ban_data['hashed_ip_address'] = nel_convert_hash_from_storage($ban_data['hashed_ip_address']);
            $ban_data['ip_address_end'] = nel_convert_ip_from_storage($ban_data['ip_address_end']);
            $ban_data['times'] = $this->secondsToTimeArray($ban_data['length']);
            $this->ban_data = $ban_data;
            return true;
        }

        return false;
    }

    public function loadFromHash(string $ban_hash)
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_BANS_TABLE . '" WHERE "ban_hash" = ?');
        $prepared->bindValue(1, nel_prepare_hash_for_storage($ban_hash), PDO::PARAM_LOB);
        $ban_data = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if ($ban_data !== false)
        {
            $this->ban_data = $ban_data;
            $ban_data['ip_type'] = intval($ban_data['ip_type']);
            $ban_data['ban_hash'] = nel_convert_hash_from_storage($ban_data['ban_hash']);
            $ban_data['ip_address_start'] = nel_convert_ip_from_storage($ban_data['ip_address_start']);
            $ban_data['hashed_ip_address'] = nel_convert_hash_from_storage($ban_data['hashed_ip_address']);
            $ban_data['ip_address_end'] = nel_convert_ip_from_storage($ban_data['ip_address_end']);
            $ban_data['times'] = $this->secondsToTimeArray($ban_data['length']);
            $this->ban_data = $ban_data;
            return true;
        }

        return false;
    }

    public function getIDFromHash(string $ban_hash)
    {
        $prepared = $this->database->prepare('SELECT "ban_id" FROM "' . NEL_BANS_TABLE . '" WHERE "ban_hash" = ?');
        $prepared->bindValue(1, $ban_hash, PDO::PARAM_LOB);
        $ban_id = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        return $ban_id;
    }

    public function apply()
    {
        if (is_null($this->ban_data['ban_id']))
        {
            $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_BANS_TABLE . '" WHERE "ban_id" = ?');
            $result = $this->database->executePreparedFetchAll($prepared, [$this->ban_data['ban_id']], PDO::FETCH_ASSOC);

            if (!$result)
            {
                $prepared = $this->database->prepare(
                        'INSERT INTO "' . NEL_BANS_TABLE .
                        '" ("ban_hash", "ban_type", "board_id", "all_boards", "ip_type", "creator", "ip_address_start", "hashed_ip_address",
                 "ip_address_end", "reason", "length", "start_time") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $prepared->bindValue(1, nel_prepare_hash_for_storage($this->ban_data['ban_hash']), PDO::PARAM_LOB);
                $prepared->bindValue(2, $this->ban_data['ban_type'], PDO::PARAM_STR);
                $prepared->bindValue(3, $this->ban_data['board'], PDO::PARAM_STR);
                $prepared->bindValue(4, $this->ban_data['all_boards'], PDO::PARAM_INT);
                $prepared->bindValue(5, $this->ban_data['ip_type'], PDO::PARAM_INT);
                $prepared->bindValue(6, $this->ban_data['creator'], PDO::PARAM_STR);
                $prepared->bindValue(7, nel_prepare_ip_for_storage($this->ban_data['ip_address_start']), PDO::PARAM_LOB);
                $prepared->bindValue(8, nel_prepare_hash_for_storage($this->ban_data['hashed_ip_address']),
                        PDO::PARAM_LOB);
                $prepared->bindValue(9, nel_prepare_ip_for_storage($this->ban_data['ip_address_end']), PDO::PARAM_LOB);
                $prepared->bindValue(10, $this->ban_data['reason'], PDO::PARAM_STR);
                $prepared->bindValue(11, $this->ban_data['length'], PDO::PARAM_INT);
                $prepared->bindValue(12, $this->ban_data['start_time'], PDO::PARAM_INT);
                $this->database->executePrepared($prepared);
            }
        }
        else
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . NEL_BANS_TABLE .
                    '" SET "ban_hash" = ?, "ban_type" = ?, "board_id" = ?, "all_boards" = ?, "ip_type" = ?, "creator" = ?,
                 "ip_address_start" = ?, "hashed_ip_address" = ?, "ip_address_end" = ?,
                 "reason" = ?, "length" = ?, "start_time" = ?, "appeal_response" = ?,
                 "appeal_status" = ? WHERE "ban_id" = ?');
            $prepared->bindValue(1, nel_prepare_hash_for_storage($this->ban_data['ban_hash']), PDO::PARAM_LOB);
            $prepared->bindValue(2, $this->ban_data['ban_type'], PDO::PARAM_STR);
            $prepared->bindValue(3, $this->ban_data['board'], PDO::PARAM_STR);
            $prepared->bindValue(4, $this->ban_data['all_boards'], PDO::PARAM_INT);
            $prepared->bindValue(5, $this->ban_data['ip_type'], PDO::PARAM_INT);
            $prepared->bindValue(6, $this->ban_data['creator'], PDO::PARAM_STR);
            $prepared->bindValue(7, nel_prepare_ip_for_storage($this->ban_data['ip_address_start']), PDO::PARAM_LOB);
            $prepared->bindValue(8, nel_prepare_hash_for_storage($this->ban_data['hashed_ip_address']), PDO::PARAM_LOB);
            $prepared->bindValue(9, nel_prepare_ip_for_storage($this->ban_data['ip_address_end']), PDO::PARAM_LOB);
            $prepared->bindValue(10, $this->ban_data['reason'], PDO::PARAM_STR);
            $prepared->bindValue(11, $this->ban_data['length'], PDO::PARAM_INT);
            $prepared->bindValue(12, $this->ban_data['start_time'], PDO::PARAM_INT);
            $prepared->bindValue(13, $this->ban_data['appeal_response'], PDO::PARAM_STR);
            $prepared->bindValue(14, $this->ban_data['appeal_status'], PDO::PARAM_INT);
            $prepared->bindValue(15, $this->ban_data['ban_id'], PDO::PARAM_INT);
            $this->database->executePrepared($prepared);
        }
    }

    public function remove()
    {
        if (!isset($this->ban_data['ban_id']))
        {
            return false;
        }

        $prepared = $this->database->prepare('DELETE FROM "' . NEL_BANS_TABLE . '" WHERE "ban_id" = ?');
        $this->database->executePrepared($prepared, [$this->ban_data['ban_id']]);
        return true;
    }

    public function addAppeal(string $appeal)
    {
        if ($this->ban_data['appeal_status'] == 0)
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . NEL_BANS_TABLE . '" SET "appeal" = ?, "appeal_status" = 1 WHERE "ban_id" = ?');
            $prepared->bindValue(1, $appeal, PDO::PARAM_STR);
            $prepared->bindValue(2, $this->ban_data['ban_id'], PDO::PARAM_INT);
            $this->database->executePrepared($prepared);
            return true;
        }

        return false;
    }
}

