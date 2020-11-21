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

    public function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function postToArray()
    {
        $ban_input = array();
        $ban_input['ban_id'] = $_POST['ban_id'] ?? null;
        $ban_input['board'] = $_POST['ban_board'] ?? null;

        if (isset($_POST['ban_all_boards_']) && is_array($_POST['ban_all_boards_']))
        {
            $ban_input['all_boards'] = nel_form_input_default($_POST['ban_all_boards_']);
        }

        $ban_input['type'] = $_POST['ban_type'] ?? 'GENERAL';
        $ban_input['creator'] = $_SESSION['user_id'] ?? null;
        $ban_input['ip_address_start'] = $_POST['ban_ip'] ?? null;

        if (isset($_POST['ban_hashed_ip']))
        {
            $ban_input['hashed_ip_address'] = $_POST['ban_hashed_ip'];
        }
        else
        {
            $ban_input['hashed_ip_address'] = hash('sha256', $ban_input['ip_address_start']);
        }

        $ban_input['years'] = $_POST['ban_time_years'] ?? 0;
        $ban_input['months'] = $_POST['ban_time_months'] ?? 0;
        $ban_input['days'] = $_POST['ban_time_days'] ?? 0;
        $ban_input['hours'] = $_POST['ban_time_hours'] ?? 0;
        $ban_input['minutes'] = $_POST['ban_time_minutes'] ?? 0;
        $ban_input['seconds'] = $_POST['ban_time_seconds'] ?? 0;
        $ban_input['start_time'] = $_POST['ban_start_time'] ?? null;
        $ban_input['reason'] = $_POST['ban_reason'] ?? null;
        //$ban_input['appeal'] = $_POST['ban_appeal'] ?? null;
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

    private function combineTimeToSeconds(array $time)
    {
        return ($time['years'] * 31536000) + ($time['months'] * 2592000) + ($time['days'] * 86400) +
                ($time['hours'] * 3600) + ($time['minutes'] * 60) + $time['seconds'];
    }

    public function getBanById($ban_id, bool $convert_length = false)
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_BANS_TABLE . '" WHERE "ban_id" = ?');
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

    public function getBansByIp(?string $ban_ip, string $hashed_ip)
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_BANS_TABLE . '" WHERE "ip_address_start" = ? OR "hashed_ip_address" = ?');
        $prepared->bindValue(1, nel_prepare_ip_for_storage($ban_ip), PDO::PARAM_LOB);
        $prepared->bindValue(2, nel_prepare_hash_for_storage($hashed_ip), PDO::PARAM_LOB);
        $ban_info = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);

        if ($ban_info === false)
        {
            return array();
        }

        return $ban_info;
    }

    public function addBan(array $ban_input)
    {
        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_BANS_TABLE .
                '" ("board_id", "all_boards", "type", "creator", "ip_address_start", "hashed_ip_address", "reason", "length", "start_time")
								VALUES (:board_id, :all_boards, :type, :creator, :ip_address_start, :hashed_ip_address, :reason, :length, :start_time)');
        $prepared->bindValue(':board_id', $ban_input['board'], PDO::PARAM_STR);
        $prepared->bindValue(':all_boards', $ban_input['all_boards'], PDO::PARAM_INT);
        $prepared->bindValue(':type', $ban_input['type'], PDO::PARAM_STR);
        $prepared->bindValue(':creator', $ban_input['creator'], PDO::PARAM_STR);
        $prepared->bindValue(':ip_address_start', nel_prepare_ip_for_storage($ban_input['ip_address_start']),
                PDO::PARAM_LOB);
        $prepared->bindValue(':hashed_ip_address', nel_prepare_hash_for_storage($ban_input['hashed_ip_address']), PDO::PARAM_LOB);
        $prepared->bindValue(':reason', $ban_input['reason'], PDO::PARAM_STR);
        $prepared->bindValue(':length', $ban_input['length'], PDO::PARAM_INT);

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
                'UPDATE "' . NEL_BANS_TABLE .
                '" SET "board_id" = :board_id, "all_boards" = :all_boards, "type" = :type, "ip_address_start" = :ip_address_start, "hashed_ip_address" = :hashed_ip_address, "reason" = :reason, "length" = :length, "start_time" = :start_time, "appeal_response" = :appeal_response, "appeal_status" = :appeal_status WHERE "ban_id" = :ban_id');
        $prepared->bindValue(':ban_id', $ban_input['ban_id'], PDO::PARAM_INT);
        $prepared->bindValue(':board_id', $ban_input['board'], PDO::PARAM_STR);
        $prepared->bindValue(':all_boards', $ban_input['all_boards'], PDO::PARAM_INT);
        $prepared->bindValue(':type', $ban_input['type'], PDO::PARAM_STR);
        $prepared->bindValue(':ip_address_start', nel_prepare_ip_for_storage($ban_input['ip_address_start']),
                PDO::PARAM_LOB);
        $prepared->bindValue(':hashed_ip_address', nel_prepare_hash_for_storage($ban_input['hashed_ip_address']), PDO::PARAM_LOB);
        $prepared->bindValue(':reason', $ban_input['reason'], PDO::PARAM_STR);
        $prepared->bindValue(':length', $ban_input['length'], PDO::PARAM_INT);
        $prepared->bindValue(':start_time', $ban_input['start_time'], PDO::PARAM_INT);
        //$prepared->bindValue(':appeal', $ban_input['appeal'], PDO::PARAM_STR);
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
            if (!$user->checkPermission($domain, 'perm_manage_bans'))
            {
                nel_derp(324, _gettext('You are not allowed to remove bans.'));
            }
        }

        $prepared = $this->database->prepare('DELETE FROM "' . NEL_BANS_TABLE . '" WHERE "ban_id" = ?');
        $this->database->executePrepared($prepared, [$ban_id]);
    }
}

