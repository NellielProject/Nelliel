<?php
declare(strict_types = 1);

namespace Nelliel\Bans;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Database\NellielPDO;
use PDO;

class BanAppeal
{
    private $database;
    private $appeal_data = array();
    private $session;
    private $appeal_id = 0;

    public function __construct(int $appeal_id, NellielPDO $database)
    {
        $this->appeal_id = $appeal_id;
        $this->database = $database;
        $this->session = new Session();
        $this->load();
    }

    public function getData(string $key)
    {
        return $this->appeal_data[$key] ?? null;
    }

    public function modifyData(string $key, $data)
    {
        $this->appeal_data[$key] = $data;
    }

    public function addFromPOST(): void
    {
        $this->appeal_data['ban_id'] = intval($_POST['ban_id'] ?? 0);

        if (empty($this->appeal_data['ban_id'])) {
            return;
        }

        $this->appeal_data['appeal'] = $_POST['bawww'] ?? '';
        $this->appeal_data['time'] = time();
        $this->appeal_data['pending'] = 1;
        $this->store();
    }

    public function updateFromPOST(): void
    {
        $appeal_id = intval($_POST['appeal_id'] ?? 0);

        if ($appeal_id !== $this->appeal_id || $this->appeal_id === 0) {
            return;
        }

        $this->appeal_data['response'] = $_POST['ban_appeal_response'] ?? '';
        $status = $_POST['appeal_status'] ?? '';
        $this->appeal_data['denied'] = ($status === 'denied') ? 1 : 0;
        $this->appeal_data['pending'] = ($status === 'unreviewed' || $status === '') ? 1 : 0;
        $this->store();
    }

    private function load(): void
    {
        if (!$this->exists()) {
            return;
        }

        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_BAN_APPEALS_TABLE . '" WHERE "appeal_id" = ?');
        $appeal_data = $this->database->executePreparedFetch($prepared, [$this->appeal_id], PDO::FETCH_ASSOC);

        if ($appeal_data !== false) {
            $this->appeal_data['ban_id'] = intval($appeal_data['ban_id']);
            $this->appeal_data['time'] = intval($appeal_data['time']);
            $this->appeal_data['appeal'] = $appeal_data['appeal'];
            $this->appeal_data['response'] = $appeal_data['response'];
            $this->appeal_data['pending'] = intval($appeal_data['pending']);
            $this->appeal_data['denied'] = intval($appeal_data['denied']);
        }
    }

    private function store(): void
    {
        if ($this->exists()) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_BAN_APPEALS_TABLE .
                '" SET "ban_id" = ?, "time" = ?, "appeal" = ?,
                 "response" = ?, "pending" = ?, "denied" = ? WHERE "appeal_id" = ?');
            $prepared->bindValue(7, $this->appeal_id, PDO::PARAM_INT);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_BAN_APPEALS_TABLE .
                '" ("ban_id", "time", "appeal", "response", "pending", "denied") VALUES (?, ?, ?, ?, ?, ?)');
        }

        $prepared->bindValue(1, $this->appeal_data['ban_id'], PDO::PARAM_INT);
        $prepared->bindValue(2, $this->appeal_data['time'] ?? time(), PDO::PARAM_INT);
        $prepared->bindValue(3, $this->appeal_data['appeal'] ?? '', PDO::PARAM_STR);
        $prepared->bindValue(4, $this->appeal_data['response'] ?? '', PDO::PARAM_STR);
        $prepared->bindValue(5, $this->appeal_data['pending'] ?? 1, PDO::PARAM_INT);
        $prepared->bindValue(6, $this->appeal_data['denied'] ?? 0, PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    private function exists(): bool
    {
        return $this->database->rowExists(NEL_BAN_APPEALS_TABLE, ['appeal_id'], [$this->appeal_id]);
    }

    public function delete(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_BANS_APPEALS_TABLE . '" WHERE "appeal_id" = ?');
        $this->database->executePrepared($prepared, [$this->appeal_id]);
    }
}

