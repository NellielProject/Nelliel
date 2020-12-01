<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class BanHammer
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

        if (isset($_POST['ban_all_boards_']) && is_array($_POST['ban_all_boards_']))
        {
            $this->ban_data['all_boards'] = nel_form_input_default($_POST['ban_all_boards_']);
        }

        $this->ban_data['type'] = intval($_POST['ban_type'] ?? self::UNSET);
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
                    break;

                case 'range':
                    $this->ban_data['hashed_ip_address'] = null;
                    break;

                case 'hash':
                    $this->ban_data['ip_address_start'] = null;
                    $this->ban_data['ip_address_end'] = null;
                    break;
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
        $this->ban_data['appeal_response'] = $_POST['ban_appeal_response'] ?? null;
        $this->ban_data['appeal_status'] = $_POST['ban_appeal_status'] ?? 0;
        $this->ban_data['length'] = $this->timeArrayToSeconds($this->ban_data['times']);
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

    public function loadFromID($ban_id)
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_BANS_TABLE . '" WHERE "ban_id" = ?');
        $ban_data = $this->database->executePreparedFetch($prepared, [$ban_id], PDO::FETCH_ASSOC);

        if ($ban_info !== false)
        {
            $this->ban_data = $ban_info;
            $this->ban_data['times'] = $this->secondsToTimeArray($this->ban_data['length']);
            return true;
        }

        return false;
    }

    // Deprecate, remove this
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

    // Working note: Move get bans functions to another class?
    public function getBansByIP(string $ban_ip)
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_BANS_TABLE . '" WHERE "ip_address_start" = ? AND "type" = ?');
        $prepared->bindValue(1, nel_prepare_ip_for_storage($ban_ip), PDO::PARAM_LOB);
        $prepared->bindValue(2, self::IP, PDO::PARAM_INT);
        $ban_info = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
        return $ban_info !== false ? $ban_info : array();
    }

    public function getBansByHashedIP(?string $ban_ip, string $hashed_ip)
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_BANS_TABLE . '" WHERE "hashed_ip_address" = ? AND "type" = ?');
        $prepared->bindValue(1, nel_prepare_ip_for_storage($ban_ip), PDO::PARAM_LOB);
        $prepared->bindValue(2, self::HASHED_IP, PDO::PARAM_INT);
        $ban_info = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);
        return $ban_info !== false ? $ban_info : array();
    }

    public function getBansByType(int $type)
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_BANS_TABLE . '" WHERE "type" = ?');
        $ban_info = $this->database->executePreparedFetchAll($prepared, [$type], PDO::FETCH_ASSOC);
        return $ban_info !== false ? $ban_info : array();
    }

    public function apply()
    {
        if (!is_null($this->ban_data['ban_id']))
        {
            $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_BANS_TABLE . '" WHERE "ban_id" = ?');
            $result = $this->database->executePreparedFetchAll($prepared, [$this->ban_data['ban_id']], PDO::FETCH_ASSOC);

            if (!$result)
            {
                $prepared = $this->database->prepare(
                        'INSERT INTO "' . NEL_BANS_TABLE .
                        '" ("board_id", "all_boards", "type", "creator", "ip_address_start", "hashed_ip_address",
                 "ip_address_end", "reason", "length", "start_time") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $prepared->bindValue(1, $this->ban_data['board'], PDO::PARAM_STR);
                $prepared->bindValue(2, $this->ban_data['all_boards'], PDO::PARAM_INT);
                $prepared->bindValue(3, $this->ban_data['type'], PDO::PARAM_INT);
                $prepared->bindValue(4, $this->ban_data['creator'], PDO::PARAM_STR);
                $prepared->bindValue(5, nel_prepare_ip_for_storage($this->ban_data['ip_address_start']), PDO::PARAM_LOB);
                $prepared->bindValue(6, nel_prepare_hash_for_storage($this->ban_data['hashed_ip_address']),
                        PDO::PARAM_LOB);
                $prepared->bindValue(7, nel_prepare_ip_for_storage($this->ban_data['ip_address_end']), PDO::PARAM_LOB);
                $prepared->bindValue(8, $this->ban_data['reason'], PDO::PARAM_STR);
                $prepared->bindValue(9, $this->ban_data['length'], PDO::PARAM_INT);
                $prepared->bindValue(10, $this->ban_data['start_time'], PDO::PARAM_INT);
                $this->database->executePrepared($prepared);
            }
        }
        else
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . NEL_BANS_TABLE .
                    '" SET "board_id" = ?, "all_boards" = ?, "type" = ?, "creator" = ?,
                 "ip_address_start" = ?, "hashed_ip_address" = ?, "ip_address_end" = ?,
                 "reason" = ?, "length" = ?, "start_time" = ?, "appeal_response" = ?,
                 "appeal_status" = ? WHERE "ban_id" = ?');
            $prepared->bindValue(1, $this->ban_data['board'], PDO::PARAM_STR);
            $prepared->bindValue(2, $this->ban_data['all_boards'], PDO::PARAM_INT);
            $prepared->bindValue(3, $this->ban_data['type'], PDO::PARAM_INT);
            $prepared->bindValue(4, $this->ban_data['creator'], PDO::PARAM_STR);
            $prepared->bindValue(5, nel_prepare_ip_for_storage($this->ban_data['ip_address_start']), PDO::PARAM_LOB);
            $prepared->bindValue(6, nel_prepare_hash_for_storage($this->ban_data['hashed_ip_address']), PDO::PARAM_LOB);
            $prepared->bindValue(7, nel_prepare_ip_for_storage($this->ban_data['ip_address_end']), PDO::PARAM_LOB);
            $prepared->bindValue(8, $this->ban_data['reason'], PDO::PARAM_STR);
            $prepared->bindValue(9, $this->ban_data['length'], PDO::PARAM_INT);
            $prepared->bindValue(10, $this->ban_data['start_time'], PDO::PARAM_INT);
            $prepared->bindValue(11, $this->ban_data['appeal_response'], PDO::PARAM_STR);
            $prepared->bindValue(12, $this->ban_data['appeal_status'], PDO::PARAM_INT);
            $prepared->bindValue(13, $this->ban_data['ban_id'], PDO::PARAM_INT);
            $this->database->executePrepared($prepared);
        }
    }

    public function removeBan($ban_id)
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_BANS_TABLE . '" WHERE "ban_id" = ?');
        $this->database->executePrepared($prepared, [$ban_id]);
    }
}

