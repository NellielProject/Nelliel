<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use IPTools\Range;
use Nelliel\Account\Session;
use Nelliel\Domains\Domain;
use Exception;
use PDO;

class BanHammer
{
    private $database;
    private $ban_data = array();
    private $session;

    public function __construct(NellielPDO $database)
    {
        $this->database = $database;
        $this->session = new Session();
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
        $ban_id = $_POST['ban_id'] ?? null;
        $existing_ban = (!is_null($ban_id)) ? $this->loadFromID($ban_id) : false;

        if ($existing_ban) {
            $delete_ban = $_POST['delete_ban'] ?? 0;

            if (is_array($delete_ban)) {
                $delete_ban = nel_form_input_default($delete_ban);

                if ($delete_ban > 0) {
                    $this->delete();
                    return;
                }
            }
        }
        $this->ban_data['board_id'] = utf8_strtolower($_POST['ban_board'] ?? $this->ban_data['board_id'] ?? null);
        $this->ban_data['seen'] = $_POST['ban_seen'] ?? $this->ban_data['seen'] ?? 0;
        $global = $_POST['ban_global'] ?? 0;

        if (is_array($global)) {
            $global = nel_form_input_default($global);
        }

        if ($global > 0) {
            $this->ban_data['board_id'] = Domain::GLOBAL;
        }

        if (is_null($this->ban_data['board_id'])) {
            nel_derp(158, _gettext('No board or domain given for the ban.'));
        }

        if (empty($this->ban_data['creator'])) {
            $this->ban_data['creator'] = $_SESSION['username'];
        }

        $ip_address = $_POST['ban_ip'] ?? null;

        if (nel_true_empty($ip_address) && !$existing_ban) {
            nel_derp(155, _gettext('No IP address or hash provided.'));
        } else if (utf8_strlen($ip_address) === 32 && ctype_xdigit($ip_address)) {
            $this->ban_data['ip_address_start'] = null;
            $this->ban_data['ip_address_end'] = null;
            $this->ban_data['hashed_ip_address'] = $ip_address;
            $this->ban_data['ip_type'] = BansAccess::HASHED_IP;
        } else {
            try {
                $range = Range::parse($ip_address);
            } catch (Exception $e) {
                nel_derp(154, _gettext('IP address or hash is invalid.'));
            }

            if ((string) $range->getFirstIP() === (string) $range->getLastIP()) {
                $this->ban_data['ip_address_start'] = (string) $range->getFirstIP();
                $this->ban_data['ip_address_end'] = null;
                $this->ban_data['hashed_ip_address'] = nel_ip_hash($ip_address);
                $this->ban_data['ip_type'] = BansAccess::IP;
            } else {
                $this->ban_data['ip_address_start'] = (string) $range->getFirstIP();
                $this->ban_data['ip_address_end'] = (string) $range->getLastIP();
                $this->ban_data['hashed_ip_address'] = null;
                $this->ban_data['ip_type'] = BansAccess::RANGE;
            }
        }

        $this->ban_data['times']['years'] = $_POST['ban_time_years'] ?? $this->ban_data['times']['years'] ?? 0;
        $this->ban_data['times']['months'] = $_POST['ban_time_months'] ?? $this->ban_data['times']['months'] ?? 0;
        $this->ban_data['times']['days'] = $_POST['ban_time_days'] ?? $this->ban_data['times']['days'] ?? 0;
        $this->ban_data['times']['hours'] = $_POST['ban_time_hours'] ?? $this->ban_data['times']['hours'] ?? 0;
        $this->ban_data['times']['minutes'] = $_POST['ban_time_minutes'] ?? $this->ban_data['times']['minutes'] ?? 0;
        $this->ban_data['times']['seconds'] = $_POST['ban_time_seconds'] ?? $this->ban_data['times']['seconds'] ?? 0;

        if (empty($this->ban_data['start_time']) && !isset($_POST['ban_start_time'])) {
            $this->ban_data['start_time'] = time();
        } else {
            $this->ban_data['start_time'] = $_POST['ban_start_time'] ?? $this->ban_data['start_time'];
        }

        $this->ban_data['reason'] = $_POST['ban_reason'] ?? $this->ban_data['reason'] ?? null;

        if (empty($this->ban_data['start_time']) && !isset($_POST['ban_start_time'])) {
            $this->ban_data['start_time'] = time();
        } else {
            $this->ban_data['start_time'] = $_POST['ban_start_time'] ?? $this->ban_data['start_time'];
        }

        $this->ban_data['length'] = $this->timeArrayToSeconds($this->ban_data['times']);
        $this->ban_data['appeal_allowed'] = $_POST['appeal_allowed'] ?? 0;

        if (is_array($this->ban_data['appeal_allowed'])) {
            $this->ban_data['appeal_allowed'] = nel_form_input_default($this->ban_data['appeal_allowed']);
        }
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

        if ($ban_data !== false) {
            $ban_data['ip_type'] = intval($ban_data['ip_type']);
            $ban_data['ip_address_start'] = nel_convert_ip_from_storage($ban_data['ip_address_start']);
            $ban_data['hashed_ip_address'] = $ban_data['hashed_ip_address'];
            $ban_data['ip_address_end'] = nel_convert_ip_from_storage($ban_data['ip_address_end']);
            $ban_data['times'] = $this->secondsToTimeArray($ban_data['length']);
            $this->ban_data = $ban_data;
            return true;
        }

        return false;
    }

    public function apply()
    {
        $ban_id = $this->ban_data['ban_id'] ?? null;
        $unhashed_check = $this->ban_data['ip_type'] != BansAccess::RANGE;

        if (is_null($ban_id)) {
            $prepared = $this->database->prepare('SELECT 1 FROM "' . NEL_BANS_TABLE . '" WHERE "ban_id" = ?');
            $result = $this->database->executePreparedFetchAll($prepared, [$ban_id], PDO::FETCH_ASSOC);

            if (!$result) {
                $prepared = $this->database->prepare(
                    'INSERT INTO "' . NEL_BANS_TABLE .
                    '" ("board_id", "ip_type", "creator", "ip_address_start", "hashed_ip_address",
                 "ip_address_end", "reason", "start_time", "length", "seen", "appeal_allowed") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $prepared->bindValue(1, $this->ban_data['board_id'], PDO::PARAM_STR);
                $prepared->bindValue(2, $this->ban_data['ip_type'], PDO::PARAM_INT);
                $prepared->bindValue(3, $this->ban_data['creator'], PDO::PARAM_STR);
                $prepared->bindValue(4, nel_prepare_ip_for_storage($this->ban_data['ip_address_start'] ?? '', $unhashed_check),
                    PDO::PARAM_LOB);
                $prepared->bindValue(5, $this->ban_data['hashed_ip_address'], PDO::PARAM_STR);
                $prepared->bindValue(6, nel_prepare_ip_for_storage($this->ban_data['ip_address_end'] ?? '', $unhashed_check),
                    PDO::PARAM_LOB);
                $prepared->bindValue(7, $this->ban_data['reason'], PDO::PARAM_STR);
                $prepared->bindValue(8, $this->ban_data['start_time'], PDO::PARAM_INT);
                $prepared->bindValue(9, $this->ban_data['length'], PDO::PARAM_INT);
                $prepared->bindValue(10, $this->ban_data['seen'], PDO::PARAM_INT);
                $prepared->bindValue(11, $this->ban_data['appeal_allowed'], PDO::PARAM_INT);

                $this->database->executePrepared($prepared);
            }
        } else {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_BANS_TABLE .
                '" SET "board_id" = ?, "ip_type" = ?, "creator" = ?,
                 "ip_address_start" = ?, "hashed_ip_address" = ?, "ip_address_end" = ?,
                 "reason" = ?, "start_time" = ?, "length" = ?, "seen" = ?, "appeal_allowed" = ? WHERE "ban_id" = ?');
            $prepared->bindValue(1, $this->ban_data['board_id'], PDO::PARAM_STR);
            $prepared->bindValue(2, $this->ban_data['ip_type'], PDO::PARAM_INT);
            $prepared->bindValue(3, $this->ban_data['creator'], PDO::PARAM_STR);
            $prepared->bindValue(4, nel_prepare_ip_for_storage($this->ban_data['ip_address_start'] ?? '', $unhashed_check),
                PDO::PARAM_LOB);
            $prepared->bindValue(5, $this->ban_data['hashed_ip_address'], PDO::PARAM_STR);
            $prepared->bindValue(6, nel_prepare_ip_for_storage($this->ban_data['ip_address_end'] ?? '', $unhashed_check),
                PDO::PARAM_LOB);
            $prepared->bindValue(7, $this->ban_data['reason'], PDO::PARAM_STR);
            $prepared->bindValue(8, $this->ban_data['start_time'], PDO::PARAM_INT);
            $prepared->bindValue(9, $this->ban_data['length'], PDO::PARAM_INT);
            $prepared->bindValue(10, $this->ban_data['seen'], PDO::PARAM_INT);
            $prepared->bindValue(11, $this->ban_data['appeal_allowed'], PDO::PARAM_INT);
            $prepared->bindValue(12, $this->ban_data['ban_id'], PDO::PARAM_INT);
            $this->database->executePrepared($prepared);
        }
    }

    public function delete()
    {
        if (!isset($this->ban_data['ban_id'])) {
            return false;
        }

        $prepared = $this->database->prepare('DELETE FROM "' . NEL_BANS_TABLE . '" WHERE "ban_id" = ?');
        $this->database->executePrepared($prepared, [$this->ban_data['ban_id']]);
        return true;
    }

    public function addAppeal(string $appeal): void
    {
        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_BAN_APPEALS_TABLE .
            '" ("ban_id", "time", "appeal", "pending") VALUES (:ban_id, :time, :appeal, :pending)');
        $prepared->bindValue(':ban_id', $this->ban_data['ban_id'], PDO::PARAM_INT);
        $prepared->bindValue(':time', time(), PDO::PARAM_INT);
        $prepared->bindValue(':appeal', $appeal, PDO::PARAM_STR);
        $prepared->bindValue(':pending', 1, PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    public function updateAppealFromPOST(): void
    {
        $appeal_id = intval($_POST['appeal_id'] ?? null);

        if (is_null($appeal_id)) {
            return;
        }

        $appeal_response = $_POST['ban_appeal_response'] ?? '';
        $appeal_denied = $_POST['appeal_denied'] ?? 0;

        if (is_array($appeal_denied)) {
            $appeal_denied = nel_form_input_default($appeal_denied);
        }

        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_BAN_APPEALS_TABLE .
            '" SET "response" = :response, "pending" = 0, "denied" = :denied WHERE "appeal_id" = :appeal_id');
        $prepared->bindValue(':response', $appeal_response, PDO::PARAM_STR);
        $prepared->bindValue(':denied', $appeal_denied, PDO::PARAM_INT);
        $prepared->bindValue(':appeal_id', $appeal_id, PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    public function appealCount(): int
    {
        $prepared = $this->database->prepare(
            'SELECT COUNT(*) FROM "' . NEL_BAN_APPEALS_TABLE . '" WHERE "ban_id" = :ban_id');
        $prepared->bindValue(':ban_id', $this->ban_data['ban_id'], PDO::PARAM_INT);
        $count = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        return intval($count);
    }

    public function appealPending(): bool
    {
        $prepared = $this->database->prepare(
            'SELECT 1 FROM "' . NEL_BAN_APPEALS_TABLE . '" WHERE "ban_id" = :ban_id AND "pending" = 1');
        $prepared->bindValue(':ban_id', $this->ban_data['ban_id'], PDO::PARAM_INT);
        $found = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_COLUMN);
        return boolval($found);
    }
}

