<?php
declare(strict_types = 1);

namespace Nelliel\FrontEnd;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Interfaces\MutableData;
use Nelliel\Interfaces\SelfPersisting;
use Nelliel\Tables\TableContentOps;
use PDO;

class ContentOp implements SelfPersisting, MutableData
{
    private NellielPDO $database;
    private string $op_id = '';
    private array $data = array();

    function __construct(NellielPDO $database, string $op_id)
    {
        $this->database = $database;
        $this->op_id = $op_id;
        $this->load();
    }

    public function id(): string
    {
        return $this->op_id;
    }

    public function getData(string $key = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        return $this->data[$key] ?? null;
    }

    public function changeData(string $key, $new_data): void
    {
        $this->data[$key] = TableContentOps::typeCastValue($key, $new_data);
    }

    public function load(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_CONTENT_OPS_TABLE . '" WHERE "op_id" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);

        if ($result === false) {
            return;
        }

        $this->data = TableContentOps::typeCastData($result);
    }

    public function save(): void
    {
        if ($this->database->rowExists(NEL_CONTENT_OPS_TABLE, ['op_id'], [$this->id()],
            [PDO::PARAM_STR, PDO::PARAM_STR])) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_CONTENT_OPS_TABLE .
                '" SET "label" = "label, "url" = :url, "images_only" = :images_only, "enabled" = :enabled, "notes" = :notes, "moar" = :moar WHERE "op_id" = :op_id');
            $prepared->bindValue(':op_id', $this->id(), PDO::PARAM_INT);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_CONTENT_OPS_TABLE .
                '" {"label". "url", "images_only", "enabled", "notes", "moar") VALUES (:label, :url, :images_only, :enabled, :notes, :moar');
        }

        $prepared->bindValue(':label', $this->getData('label') ?? '', PDO::PARAM_STR);
        $prepared->bindValue(':url', $this->getData('url') ?? '', PDO::PARAM_STR);
        $prepared->bindValue(':images_only', $this->getData('images_only') ?? 0, PDO::PARAM_INT);
        $prepared->bindValue(':enabled', $this->getData('enabled') ?? 0, PDO::PARAM_INT);
        $prepared->bindValue(':notes', $this->getData('notes'), PDO::PARAM_STR);
        $prepared->bindValue(':moar', $this->getData('moar'), PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }

    public function delete(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_CONTENT_OPS_TABLE . '" WHERE "op_id" = :op_id');
        $prepared->bindValue(':op_id', $this->id(), PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }
}
