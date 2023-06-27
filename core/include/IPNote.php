<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use PDO;

class IPNote
{
    private $database;
    private $data = array();
    private $id;

    function __construct(NellielPDO $database, int $id = 0)
    {
        $this->database = $database;
        $this->id = $id;
        $this->load();
    }

    private function load(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_IP_NOTES_TABLE . '" WHERE "note_id" = :note_id');
        $prepared->bindValue(':note_id', $this->id, PDO::PARAM_INT);
        $result = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if ($result != false) {
            $this->data = $result;
        }
    }

    public function update(string $username, string $ip_address, string $notes): bool
    {
        $ip_info = new IPInfo($ip_address);

        if (empty($this->id) || !$this->database->rowExists(NEL_IP_NOTES_TABLE, ['note_id'], [$this->id])) {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_IP_NOTES_TABLE .
                '" ("username", "hashed_ip_address", "time", "notes") VALUES (:username, :hashed_ip_address, :time, :notes)');
        } else {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_IP_NOTES_TABLE .
                '" SET "username" = :username, "hashed_ip_address" = :hashed_ip_address, "time" = :time, "notes" = :notes WHERE "note_id" = :note_id');
            $prepared->bindValue(':note_id', $this->id, PDO::PARAM_INT);
        }

        $prepared->bindValue(':username', $username, PDO::PARAM_STR);
        $prepared->bindValue(':hashed_ip_address', $ip_info->getInfo('hashed_ip_address'), PDO::PARAM_STR);
        $prepared->bindValue(':time', time(), PDO::PARAM_INT);
        $prepared->bindValue(':notes', $notes, PDO::PARAM_STR);
        $this->database->executePrepared($prepared);

        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_IP_NOTES_TABLE .
            '" WHERE "username" = :username AND "hashed_ip_address" = :hashed_ip_address AND "time" = :time AND "notes" = :notes');
        $prepared->bindValue(':username', $username, PDO::PARAM_STR);
        $prepared->bindValue(':hashed_ip_address', $ip_info->getInfo('hashed_ip_address'), PDO::PARAM_STR);
        $prepared->bindValue(':time', time(), PDO::PARAM_INT);
        $prepared->bindValue(':notes', $notes, PDO::PARAM_STR);
        $result = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if ($result != false) {
            $this->id = intval($result['note_id']);
            $this->data = $result;
        }

        return true;
    }

    public function delete(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_IP_NOTES_TABLE . '" WHERE "note_id" = :note_id');
        $prepared->bindValue(':note_id', $this->id, PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    public function getData(string $key)
    {
        return $this->data[$key] ?? null;
    }
}