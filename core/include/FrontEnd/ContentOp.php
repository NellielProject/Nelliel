<?php
declare(strict_types = 1);

namespace Nelliel\FrontEnd;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Tables\TableContentOps;
use PDO;

class ContentOp
{
    private $database;
    private $op_id;
    private $data = array();
    private $front_end_data;

    function __construct(NellielPDO $database, FrontEndData $front_end_data, string $op_id)
    {
        $this->database = $database;
        $this->op_id = $op_id;
        $this->front_end_data = $front_end_data;
        $this->load();
    }

    public function id(): string
    {
        return $this->op_id ?? '';
    }

    public function data(string $key): string
    {
        return $this->data[$key] ?? '';
    }

    public function update(): void
    {
        if ($this->database->rowExists(NEL_CONTENT_OPS_TABLE, ['op_id'], [$this->id()],
            [PDO::PARAM_STR, PDO::PARAM_STR])) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_CONTENT_OPS_TABLE .
                '" SET "label" = ?, "url" = ?, "images_only" = ?, "enabled" = ?, "notes" = ?, "moar" = ? WHERE "op_id" = ?');
            $this->database->executePrepared($prepared,
                [$this->data('label'), $this->data('url'), $this->data('images_only'), $this->data('enabled'),
                    $this->data('notes'), $this->data('moar'), $this->id()]);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_CONTENT_OPS_TABLE .
                '" {"label". "url", "images_only", "enabled", "notes", "moar") VALUES (?, ?, ?, ?, ?, ?');
            $this->database->executePrepared($prepared,
                [$this->data('label'), $this->data('url'), $this->data('images_only'), $this->data('enabled'),
                    $this->data('notes'), $this->data('moar'), $this->id()]);
        }

        $this->load();
    }

    public function delete(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_CONTENT_OPS_TABLE . '" WHERE "op_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
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
}
