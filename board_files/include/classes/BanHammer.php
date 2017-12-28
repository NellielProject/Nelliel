<?php

namespace Nelliel;

use \PDO;
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class BanHammer
{
    private $dbh;
    private $authorize;

    public function __construct()
    {
        $this->dbh = nel_database();
        $this->authorize = nel_authorize();
    }

    public function postToArray()
    {
        $ban_input = array();
        $ban_input['ban_id'] = (isset($_POST['ban_id'])) ? $_POST['ban_id'] : null;
        $ban_input['board'] = (isset($_POST['ban_board'])) ? $_POST['ban_board'] : null;
        $ban_input['type'] = (isset($_POST['ban_type'])) ? $_POST['ban_type'] : null;
        $ban_input['ip_address'] = (isset($_POST['ban_ip'])) ? $_POST['ban_ip'] : null;
        $ban_input['years'] = (isset($_POST['ban_time_years'])) ? $_POST['ban_time_years'] : 0;
        $ban_input['months'] = (isset($_POST['ban_time_months'])) ? $_POST['ban_time_months'] : 0;
        $ban_input['days'] = (isset($_POST['ban_time_days'])) ? $_POST['ban_time_days'] : 0;
        $ban_input['hours'] = (isset($_POST['ban_time_hours'])) ? $_POST['ban_time_hours'] : 0;
        $ban_input['minutes'] = (isset($_POST['ban_time_minutes'])) ? $_POST['ban_time_minutes'] : 0;
        $ban_input['seconds'] = (isset($_POST['ban_time_seconds'])) ? $_POST['ban_time_seconds'] : 0;
        $ban_input['start_time'] = (isset($_POST['ban_start_time'])) ? $_POST['ban_start_time'] : null;
        $ban_input['reason'] = (isset($_POST['ban_reason'])) ? $_POST['ban_reason'] : null;
        $ban_input['appeal'] = (isset($_POST['ban_appeal'])) ? $_POST['ban_appeal'] : null;
        $ban_input['appeal_response'] = (isset($_POST['ban_appeal_response'])) ? $_POST['ban_appeal_response'] : null;
        $ban_input['appeal_status'] = (isset($_POST['ban_appeal_status'])) ? $_POST['ban_appeal_status'] : null;
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

    public function getBanById($ban_id, $convert_length = false)
    {
        $prepared = $this->dbh->prepare('SELECT * FROM "' . BAN_TABLE . '" WHERE "ban_id" = ? LIMIT 1');
        $ban_info = $this->dbh->executePreparedFetch($prepared, array($ban_id), PDO::FETCH_ASSOC);

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

    public function getBanByIp($ban_ip, $convert_length = false)
    {
        $prepared = $this->dbh->prepare('SELECT * FROM "' . BAN_TABLE . '" WHERE "ip_address" = ? LIMIT 1');
        $ban_info = $this->dbh->executePreparedFetch($prepared, array($ban_ip), PDO::FETCH_ASSOC);

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

    public function addBan($ban_input)
    {
        if (!$this->authorize->get_user_perm($_SESSION['username'], 'perm_ban_add'))
        {
            nel_derp(321, nel_stext('ERROR_321'));
        }

        $prepared = $this->dbh->prepare('INSERT INTO "' . BAN_TABLE . '" ("board", "type", "ip_address", "reason", "length", "start_time")
								VALUES (:board, :type, :ip_address, :reason, :length, :start_time)');
        $prepared->bindParam(':board', $ban_input['board'], PDO::PARAM_STR);
        $prepared->bindParam(':type', $ban_input['type'], PDO::PARAM_STR);
        $prepared->bindParam(':ip_address', $ban_input['ip_address'], PDO::PARAM_STR);
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

        $this->dbh->executePrepared($prepared);
    }

    public function modifyBan($ban_input)
    {
        if (!$this->authorize->get_user_perm($_SESSION['username'], 'perm_ban_modify'))
        {
            nel_derp(322, nel_stext('ERROR_322'));
        }

        $prepared = $this->dbh->prepare('UPDATE "' . BAN_TABLE .
             '" SET "board" = :board, "type" = :type, "ip_address" = :ip_address, "reason" = :reason, "length" = :length, "start_time" = :start_time, "appeal" = :appeal, "appeal_response" = :appeal_response, "appeal_status" = :appeal_status WHERE "ban_id" = :ban_id');
        $prepared->bindParam(':ban_id', $ban_input['ban_id'], PDO::PARAM_INT);
        $prepared->bindParam(':board', $ban_input['board'], PDO::PARAM_STR);
        $prepared->bindParam(':type', $ban_input['type'], PDO::PARAM_STR);
        $prepared->bindParam(':ip_address', $ban_input['ip_address'], PDO::PARAM_STR);
        $prepared->bindParam(':reason', $ban_input['reason'], PDO::PARAM_STR);
        $prepared->bindParam(':length', $ban_input['length'], PDO::PARAM_INT);
        $prepared->bindValue(':start_time', $ban_input['start_time'], PDO::PARAM_INT);
        $prepared->bindValue(':appeal', $ban_input['appeal'], PDO::PARAM_STR);
        $prepared->bindValue(':appeal_response', $ban_input['appeal_response'], PDO::PARAM_STR);
        $prepared->bindValue(':appeal_status', $ban_input['appeal_status'], PDO::PARAM_INT);
        $this->dbh->executePrepared($prepared);
    }

    public function removeBan($ban_id, $snacks = false)
    {
        if (!$this->authorize->get_user_perm($_SESSION['username'], 'perm_ban_delete') && !$snacks)
        {
            nel_derp(323, nel_stext('ERROR_323'));
        }

        $prepared = $this->dbh->prepare('DELETE FROM "' . BAN_TABLE . '" WHERE "ban_id" = ?');
        $this->dbh->executePrepared($prepared, array($ban_id));
    }
}

