<?php
declare(strict_types = 1);

namespace Nelliel\Filters;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use PDO;
use Nelliel\Domains\Domain;

class FileFilter
{
    private $database;
    private $data = array();
    private $id;

    function __construct(NellielPDO $database, int $id)
    {
        $this->database = $database;
        $this->id = $id;
        $this->load();
    }

    private function load(): void
    {
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_FILE_FILTERS_TABLE . '" WHERE "filter_id" = :filter_id');
        $prepared->bindValue(':filter_id', $this->id, PDO::PARAM_INT);
        $result = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if ($result != false) {
            $this->data = $result;
        }
    }

    public function update(): void
    {
        if (!$this->database->rowExists(NEL_FILE_FILTERS_TABLE, ['filter_id'], [$this->id])) {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_FILE_FILTERS_TABLE .
                '" ("board_id", "hash_type", "file_hash", "filter_action", "notes", "enabled") VALUES (:board_id, :hash_type, :file_hash, :filter_action, :notes, :enabled)');
        } else {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_FILE_FILTERS_TABLE .
                '" SET "board_id" = :board_id, "hash_type" = :hash_type, "file_hash" = :file_hash, "filter_action" = :filter_action, "notes" = :notes,  "enabled" = :enabled WHERE "filter_id" = :filter_id');
            $prepared->bindValue(':filter_id', $this->id, PDO::PARAM_INT);
        }

        $board = Domain::getDomainFromID($this->getData('board_id') ?? '', $this->database);
        $prepared->bindValue(':board_id', $board->id(), PDO::PARAM_STR);
        $prepared->bindValue(':hash_type', $this->getData('hash_type') ?? '', PDO::PARAM_STR);
        $prepared->bindValue(':file_hash', $this->getData('file_hash'), PDO::PARAM_STR);
        $prepared->bindValue(':filter_action', $this->getData('filter_action'), PDO::PARAM_STR);
        $prepared->bindValue(':notes', $this->getData('notes'), PDO::PARAM_STR);
        $prepared->bindValue(':enabled', $this->getData('enabled'), PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }

    public function delete(): void
    {
        $prepared = $this->database->prepare(
            'DELETE FROM "' . NEL_FILE_FILTERS_TABLE . '" WHERE "filter_id" = :filter_id');
        $prepared->bindValue(':filter_id', $this->id, PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    public function getData(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function changeData(string $key, $new_data): void
    {
        $this->data[$key] = $new_data;
    }
}