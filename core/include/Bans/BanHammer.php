<?php
declare(strict_types = 1);

namespace Nelliel\Bans;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use IPTools\Range;
use Nelliel\IPInfo;
use Nelliel\Account\Session;
use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use Exception;
use PDO;

class BanHammer
{
    private $database;
    private $ban_data = array();
    private $session;
    private $appeals = array();
    private $active_appeal;
    private $ban_id;

    public function __construct(NellielPDO $database, int $ban_id = 0)
    {
        $this->database = $database;
        $this->ban_id = $ban_id;
        $this->session = new Session();
        $this->load();
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
        $existing_ban = $this->exists();

        $this->ban_id = intval($_POST['ban_id'] ?? 0);

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
        $this->ban_data['seen'] = $this->ban_data['seen'] ?? 0;
        $global = $_POST['ban_global'] ?? 0;

        if (is_array($global)) {
            $global = nel_form_input_default($global);
        }

        if ($global > 0 || $this->ban_data['board_id'] === Domain::GLOBAL) {
            $this->ban_data['board_id'] = Domain::GLOBAL;
        } else {
            $board_domain = new DomainBoard($this->ban_data['board_id'], $this->database);

            if (is_null($this->ban_data['board_id']) || !$board_domain->exists()) {
                nel_derp(158, _gettext('No valid board given for the ban.'));
            }
        }

        if (empty($this->ban_data['creator'])) {
            $this->ban_data['creator'] = $this->session->user()->id();
        }

        $ip_address = $_POST['ban_ip'] ?? null;

        if (nel_true_empty($ip_address) && !$existing_ban) {
            nel_derp(155, _gettext('No IP address or hash provided.'));
        }

        $type = $_POST['ban_type'] ?? '';
        $this->ban_data['ip_address'] = null;
        $this->ban_data['hashed_ip_address'] = null;
        $this->ban_data['range_start'] = null;
        $this->ban_data['range_end'] = null;
        $this->ban_data['hashed_subnet'] = null;
        $invalid = false;
        $ip_info = new IPInfo($ip_address);

        if ($type === 'ip') {
            $this->ban_data['ban_type'] = nel_is_unhashed_ip($ip_address) ? BansAccess::IP : BansAccess::HASHED_IP;
            $this->ban_data['ip_address'] = $ip_info->getInfo('ip_address');
            $this->ban_data['hashed_ip_address'] = $ip_info->getInfo('hashed_ip_address');
        } else if ($type === 'subnet') {
            $this->ban_data['hashed_subnet'] = $ip_address;
            $this->ban_data['ban_type'] = BansAccess::HASHED_SUBNET;
        } else if ($type === 'small_subnet') {
            $this->ban_data['hashed_subnet'] = $ip_info->getInfo('hashed_small_subnet');
            $this->ban_data['ban_type'] = BansAccess::HASHED_SUBNET;
        } else if ($type === 'large_subnet') {
            $this->ban_data['hashed_subnet'] = $ip_info->getInfo('hashed_large_subnet');
            $this->ban_data['ban_type'] = BansAccess::HASHED_SUBNET;
        } else if ($type === 'range') {
            try {
                $range = Range::parse($ip_address);
                $this->ban_data['ban_type'] = BansAccess::RANGE;
            } catch (Exception $e) {
                $invalid = true;
            }

            $this->ban_data['range_start'] = (string) $range->getFirstIP();
            $this->ban_data['range_end'] = (string) $range->getLastIP();
        } else {
            $invalid = true;
        }

        if ($invalid) {
            nel_derp(154, _gettext('Provided IP or hash is invalid or you selected the wrong type.'));
        }

        $time = $_POST['ban_length'] ?? '';
        $length = 0;

        if (is_numeric($time)) {
            $time = $time . 'seconds';
        }

        $length = nel_strtotime($time) - time();

        if ($length < 1) {
            nel_derp(150, _gettext('Invalid ban length given.'));
        }

        $this->ban_data['length'] = $length;

        if (empty($this->ban_data['start_time'])) {
            $this->ban_data['start_time'] = time();
        }

        $this->ban_data['reason'] = $_POST['ban_reason'] ?? $this->ban_data['reason'] ?? null;
        $this->ban_data['appeal_allowed'] = $_POST['appeal_allowed'] ?? 0;

        if (is_array($this->ban_data['appeal_allowed'])) {
            $this->ban_data['appeal_allowed'] = nel_form_input_default($this->ban_data['appeal_allowed']);
        }
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

    private function load()
    {
        if (!$this->exists()) {
            return;
        }

        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_BANS_TABLE . '" WHERE "ban_id" = ?');
        $ban_data = $this->database->executePreparedFetch($prepared, [$this->ban_id], PDO::FETCH_ASSOC);

        if ($ban_data !== false) {
            $this->ban_data['ban_id'] = intval($ban_data['ban_id']);
            $this->ban_data['board_id'] = $ban_data['board_id'];
            $this->ban_data['creator'] = $ban_data['creator'];
            $this->ban_data['ban_type'] = intval($ban_data['ban_type']);
            $this->ban_data['hashed_ip_address'] = $ban_data['hashed_ip_address'];
            $this->ban_data['ip_address'] = nel_convert_ip_from_storage($ban_data['ip_address']);
            $this->ban_data['hashed_subnet'] = $ban_data['hashed_subnet'];
            $this->ban_data['range_start'] = nel_convert_ip_from_storage($ban_data['range_start']);
            $this->ban_data['range_end'] = nel_convert_ip_from_storage($ban_data['range_end']);
            $this->ban_data['visitor_id'] = $ban_data['visitor_id'];
            $this->ban_data['reason'] = $ban_data['reason'];
            $this->ban_data['start_time'] = intval($ban_data['start_time']);
            $this->ban_data['length'] = intval($ban_data['length']);
            $this->ban_data['seen'] = intval($ban_data['seen']);
            $this->ban_data['appeal_allowed'] = intval($ban_data['appeal_allowed']);
            $this->ban_data['times'] = intval($ban_data['length']);

            $prepared = $this->database->prepare(
                'SELECT "appeal_id" FROM "' . NEL_BAN_APPEALS_TABLE . '" WHERE "ban_id" = ?');
            $appeal_ids = $this->database->executePreparedFetchAll($prepared, [$this->ban_id], PDO::FETCH_COLUMN);

            foreach ($appeal_ids as $appeal_id) {
                $ban_appeal = new BanAppeal((int) $appeal_id, $this->database);

                if ($ban_appeal->getData('pending')) {
                    $this->active_appeal = $ban_appeal;
                }

                $this->appeals[] = $ban_appeal;
            }
        }
    }

    public function apply()
    {
        $unhashed_check = $this->ban_data['ban_type'] != BansAccess::RANGE &&
            $this->ban_data['ban_type'] != BansAccess::HASHED_SUBNET;

        if ($this->exists()) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_BANS_TABLE .
                '" SET "board_id" = ?, "ban_type" = ?, "creator" = ?,
                 "ip_address" = ?, "hashed_ip_address" = ?, "hashed_subnet" = ?, "range_start" = ?, "range_end" = ?,
                 "reason" = ?, "start_time" = ?, "length" = ?, "seen" = ?, "appeal_allowed" = ? WHERE "ban_id" = ?');
            $prepared->bindValue(14, $this->ban_id, PDO::PARAM_INT);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_BANS_TABLE .
                '" ("board_id", "ban_type", "creator", "ip_address", "hashed_ip_address", "hashed_subnet", "range_start",
                 "range_end", "reason", "start_time", "length", "seen", "appeal_allowed") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        }

        $prepared->bindValue(1, $this->ban_data['board_id'], PDO::PARAM_STR);
        $prepared->bindValue(2, $this->ban_data['ban_type'], PDO::PARAM_INT);
        $prepared->bindValue(3, $this->ban_data['creator'] ?? null, PDO::PARAM_STR);
        $prepared->bindValue(4, nel_prepare_ip_for_storage($this->ban_data['ip_address'] ?? null, $unhashed_check),
            PDO::PARAM_LOB);
        $prepared->bindValue(5, $this->ban_data['hashed_ip_address'] ?? null, PDO::PARAM_STR);
        $prepared->bindValue(6, $this->ban_data['hashed_subnet'] ?? null, PDO::PARAM_STR);
        $prepared->bindValue(7, nel_prepare_ip_for_storage($this->ban_data['range_start'] ?? null, $unhashed_check),
            PDO::PARAM_LOB);
        $prepared->bindValue(8, nel_prepare_ip_for_storage($this->ban_data['range_end'] ?? null, $unhashed_check),
            PDO::PARAM_LOB);
        $prepared->bindValue(9, $this->ban_data['reason'] ?? __('Because reasons.'), PDO::PARAM_STR);
        $prepared->bindValue(10, $this->ban_data['start_time'], PDO::PARAM_INT);
        $prepared->bindValue(11, $this->ban_data['length'], PDO::PARAM_INT);
        $prepared->bindValue(12, $this->ban_data['seen'] ?? 0, PDO::PARAM_INT);
        $prepared->bindValue(13, $this->ban_data['appeal_allowed'] ?? 0, PDO::PARAM_INT);
        $this->database->executePrepared($prepared);

        if ($this->appealPending()) {
            $this->active_appeal->updateFromPOST();
        }
    }

    public function delete(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_BANS_TABLE . '" WHERE "ban_id" = ?');
        $this->database->executePrepared($prepared, [$this->ban_id]);
    }

    public function addAppeal(): void
    {
        $appeal = new BanAppeal(0, $this->database);
        $appeal->addFromPOST();
    }

    public function getAppeals(): array
    {
        return $this->appeals;
    }

    public function getActiveAppeal(): BanAppeal
    {
        return $this->active_appeal;
    }

    public function appealCount(): int
    {
        return count($this->appeals);
    }

    public function appealPending(): bool
    {
        return isset($this->active_appeal) && $this->active_appeal->getData('pending') == 1;
    }

    private function exists(): bool
    {
        return $this->database->rowExists(NEL_BANS_TABLE, ['ban_id'], [$this->ban_id]);
    }
}

