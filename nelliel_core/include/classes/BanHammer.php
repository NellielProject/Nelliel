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

    public function collectFromPOST()
    {
        $this->ban_data['ban_id'] = $_POST['ban_id'] ?? null;
        $this->ban_data['board'] = $_POST['ban_board'] ?? null;

        if (isset($_POST['ban_all_boards_']) && is_array($_POST['ban_all_boards_']))
        {
            $this->ban_data['all_boards'] = nel_form_input_default($_POST['ban_all_boards_']);
        }

        $this->ban_data['type'] = $_POST['ban_type'] ?? 'GENERAL';
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
        return $this->ban_data;
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
            $ban_info['times'] = $this->secondsToTimeArray($ban_info['length']);
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
                '" ("board_id", "all_boards", "type", "creator", "ip_address_start", "hashed_ip_address",
                 "ip_address_end", "reason", "length", "start_time") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $prepared->bindValue(1, $ban_input['board'], PDO::PARAM_STR);
        $prepared->bindValue(2, $ban_input['all_boards'], PDO::PARAM_INT);
        $prepared->bindValue(3, $ban_input['type'], PDO::PARAM_STR);
        $prepared->bindValue(4, $ban_input['creator'], PDO::PARAM_STR);
        $prepared->bindValue(5, nel_prepare_ip_for_storage($ban_input['ip_address_start']), PDO::PARAM_LOB);
        $prepared->bindValue(6, nel_prepare_hash_for_storage($ban_input['hashed_ip_address']), PDO::PARAM_LOB);
        $prepared->bindValue(7, nel_prepare_ip_for_storage($ban_input['ip_address_end']), PDO::PARAM_LOB);
        $prepared->bindValue(8, $ban_input['reason'], PDO::PARAM_STR);
        $prepared->bindValue(9, $ban_input['length'], PDO::PARAM_INT);
        $prepared->bindValue(10, $ban_input['start_time'], PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    public function modifyBan(array $ban_input)
    {
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_BANS_TABLE .
                '" SET "board_id" = ?, "all_boards" = ?, "type" = ?, "creator" = ?,
                 "ip_address_start" = ?, "hashed_ip_address" = ?, "ip_address_end" = ?,
                 "reason" = ?, "length" = ?, "start_time" = ?, "appeal_response" = ?,
                 "appeal_status" = ? WHERE "ban_id" = ?');
        $prepared->bindValue(1, $ban_input['board'], PDO::PARAM_STR);
        $prepared->bindValue(2, $ban_input['all_boards'], PDO::PARAM_INT);
        $prepared->bindValue(3, $ban_input['type'], PDO::PARAM_STR);
        $prepared->bindValue(4, $ban_input['creator'], PDO::PARAM_STR);
        $prepared->bindValue(5, nel_prepare_ip_for_storage($ban_input['ip_address_start']), PDO::PARAM_LOB);
        $prepared->bindValue(6, nel_prepare_hash_for_storage($ban_input['hashed_ip_address']), PDO::PARAM_LOB);
        $prepared->bindValue(7, nel_prepare_ip_for_storage($ban_input['ip_address_end']), PDO::PARAM_LOB);
        $prepared->bindValue(8, $ban_input['reason'], PDO::PARAM_STR);
        $prepared->bindValue(9, $ban_input['length'], PDO::PARAM_INT);
        $prepared->bindValue(10, $ban_input['start_time'], PDO::PARAM_INT);
        $prepared->bindValue(11, $ban_input['appeal_response'], PDO::PARAM_STR);
        $prepared->bindValue(12, $ban_input['appeal_status'], PDO::PARAM_INT);
        $prepared->bindValue(13, $ban_input['ban_id'], PDO::PARAM_INT);
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

