<?php
declare(strict_types = 1);

namespace Nelliel\Filters;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use Nelliel\Tables\TableWordfilters;
use PDO;

class Wordfilter
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
            'SELECT * FROM "' . NEL_WORDFILTERS_TABLE . '" WHERE "filter_id" = :filter_id');
        $prepared->bindValue(':filter_id', $this->id, PDO::PARAM_INT);
        $result = $this->database->executePreparedFetch($prepared, null, PDO::FETCH_ASSOC);

        if ($result !== false) {
            $this->data = TableWordfilters::typeCastData($result);
        }
    }

    public function update(): void
    {
        if (!$this->database->rowExists(NEL_WORDFILTERS_TABLE, ['filter_id'], [$this->id])) {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_WORDFILTERS_TABLE .
                '" ("board_id", "text_match", "replacement", "filter_action", "notes", "enabled") VALUES (:board_id, :text_match, :replacement, :filter_action, :notes, :enabled)');
        } else {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_WORDFILTERS_TABLE .
                '" SET "board_id" = :board_id, "text_match" = :text_match, "replacement" = :replacement, "filter_action" = :filter_action, "notes" = :notes, "enabled" = :enabled WHERE "filter_id" = :filter_id');
            $prepared->bindValue(':filter_id', $this->id, PDO::PARAM_INT);
        }

        $board = Domain::getDomainFromID($this->getData('board_id') ?? '', $this->database);
        $prepared->bindValue(':board_id', $board->id(), PDO::PARAM_STR);
        $prepared->bindValue(':text_match', $this->getData('text_match'), PDO::PARAM_STR);
        $prepared->bindValue(':replacement', $this->getData('replacement'), PDO::PARAM_STR);
        $prepared->bindValue(':filter_action', $this->getData('filter_action'), PDO::PARAM_STR);
        $prepared->bindValue(':notes', $this->getData('notes'), PDO::PARAM_STR);
        $prepared->bindValue(':enabled', $this->getData('enabled'), PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }

    public function delete(): void
    {
        $prepared = $this->database->prepare(
            'DELETE FROM "' . NEL_WORDFILTERS_TABLE . '" WHERE "filter_id" = :filter_id');
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