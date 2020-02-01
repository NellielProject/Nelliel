<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class BanHammer
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function postToArray()
    {
        $ban_input = array();
        $ban_input['ban_id'] = $_POST['ban_id'] ?? null;
        $ban_input['board'] = $_POST['ban_board'] ?? null;
        $ban_input['all_boards'] = (isset($_POST['ban_all_boards']) && $_POST['ban_all_boards'] > 0) ? 1 : 0;
        $ban_input['type'] = $_POST['ban_type'] ?? 'GENERAL';
        $ban_input['creator'] = $_SESSION['user_id'] ?? null;
        $ban_input['ip_address_start'] = $_POST['ban_ip'] ?? null;
        $ban_input['years'] = $_POST['ban_time_years'] ?? 0;
        $ban_input['months'] = $_POST['ban_time_months'] ?? 0;
        $ban_input['days'] = $_POST['ban_time_days'] ?? 0;
        $ban_input['hours'] = $_POST['ban_time_hours'] ?? 0;
        $ban_input['minutes'] = $_POST['ban_time_minutes'] ?? 0;
        $ban_input['seconds'] = $_POST['ban_time_seconds'] ?? 0;
        $ban_input['start_time'] = $_POST['ban_start_time'] ?? null;
        $ban_input['reason'] = $_POST['ban_reason'] ?? null;
        $ban_input['appeal'] = $_POST['ban_appeal'] ?? null;
        $ban_input['appeal_response'] = $_POST['ban_appeal_response'] ?? null;
        $ban_input['appeal_status'] = $_POST['ban_appeal_status'] ?? 0;
        $ban_input['length'] = $this->combineTimeToSeconds($ban_input);
        return $ban_input;
    }

    private function splitSecondsToTime($seconds)
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

    private function combineTimeToSeconds($time)
    {
        return ($time['years'] * 31536000) + ($time['months'] * 2592000) + ($time['days'] * 86400) +
                ($time['hours'] * 3600) + ($time['minutes'] * 60) + $time['seconds'];
    }

    public function getBanById($ban_id, bool $convert_length = false)
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . BANS_TABLE . '" WHERE "ban_id" = ?');
        $ban_info = $this->database->executePreparedFetch($prepared, [$ban_id], PDO::FETCH_ASSOC);

        if ($ban_info === false)
        {
            return null;
        }

        if ($convert_length)
        {
            $ban_info = array_merge($ban_info, $this->splitSecondsToTime($ban_info['length']));
        }

        return $ban_info;
    }

    public function getBansByIp($ban_ip)
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . BANS_TABLE . '" WHERE "ip_address_start" = :ip_address_start');
        $prepared->bindValue(':ip_address_start', @inet_pton($ban_ip), PDO::PARAM_LOB);
        $ban_info = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);

        if ($ban_info === false)
        {
            return null;
        }

        return $ban_info;
    }

    public function addBan(array $ban_input)
    {
        $prepared = $this->database->prepare(
                'INSERT INTO "' . BANS_TABLE . '" ("board_id", "all_boards", "type", "creator", "ip_address_start", "reason", "length", "start_time")
								VALUES (:board_id, :all_boards, :type, :creator, :ip_address_start, :reason, :length, :start_time)');
        $prepared->bindParam(':board_id', $ban_input['board'], PDO::PARAM_STR);
        $prepared->bindParam(':all_boards', $ban_input['all_boards'], PDO::PARAM_INT);
        $prepared->bindParam(':type', $ban_input['type'], PDO::PARAM_STR);
        $prepared->bindParam(':creator', $ban_input['creator'], PDO::PARAM_STR);
        $encoded_ip = @inet_pton($ban_input['ip_address_start']);
        $prepared->bindParam(':ip_address_start', $encoded_ip, PDO::PARAM_LOB);
        $prepared->bindParam(':reason', $ban_input['reason'], PDO::PARAM_STR);
        $prepared->bindParam(':length', $ban_input['length'], PDO::PARAM_INT);

        if (!is_null($ban_input['start_time']))
        {
            $prepared->bindValue(':start_time', $ban_input['start_time'], PDO::PARAM_INT);
        }
        else
        {
            $prepared->bindValue(':start_time', time(), PDO::PARAM_INT);
        }

        $this->database->executePrepared($prepared);
    }

    public function modifyBan(array $ban_input)
    {
        $prepared = $this->database->prepare(
                'UPDATE "' . BANS_TABLE .
                '" SET "board_id" = :board_id, "all_boards" = :all_boards, "type" = :type, "ip_address_start" = :ip_address_start, "reason" = :reason, "length" = :length, "start_time" = :start_time, "appeal" = :appeal, "appeal_response" = :appeal_response, "appeal_status" = :appeal_status WHERE "ban_id" = :ban_id');
        $prepared->bindParam(':ban_id', $ban_input['ban_id'], PDO::PARAM_INT);
        $prepared->bindParam(':board_id', $ban_input['board'], PDO::PARAM_STR);
        $prepared->bindParam(':all_boards', $ban_input['all_boards'], PDO::PARAM_INT);
        $prepared->bindParam(':type', $ban_input['type'], PDO::PARAM_STR);
        $encoded_ip = @inet_pton($ban_input['ip_address_start']);
        $prepared->bindParam(':ip_address_start', $encoded_ip, PDO::PARAM_LOB);
        $prepared->bindParam(':reason', $ban_input['reason'], PDO::PARAM_STR);
        $prepared->bindParam(':length', $ban_input['length'], PDO::PARAM_INT);
        $prepared->bindValue(':start_time', $ban_input['start_time'], PDO::PARAM_INT);
        $prepared->bindValue(':appeal', $ban_input['appeal'], PDO::PARAM_STR);
        $prepared->bindValue(':appeal_response', $ban_input['appeal_response'], PDO::PARAM_STR);
        $prepared->bindValue(':appeal_status', $ban_input['appeal_status'], PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    public function removeBan(Domain $domain, $ban_id, bool $snacks = false)
    {
        $session = new \Nelliel\Account\Session();
        $user = $session->sessionUser();

        if (!$snacks)
        {
            if (!$user->domainPermission($domain, 'perm_manage_bans', true) && !$snacks)
            {
                nel_derp(321, _gettext('You are not allowed to modify bans.'));
            }
        }

        $prepared = $this->database->prepare('DELETE FROM "' . BANS_TABLE . '" WHERE "ban_id" = ?');
        $this->database->executePrepared($prepared, [$ban_id]);
    }
}

