<?php
declare(strict_types = 1);

namespace Nelliel\FrontEnd;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;
use PDO;

class ContentOp
{
    private $database;
    private $content_op_id;
    private $data = array();
    private $front_end_data;

    function __construct(NellielPDO $database, FrontEndData $front_end_data, string $content_op_id)
    {
        $this->database = $database;
        $this->content_op_id = $content_op_id;
        $this->front_end_data = $front_end_data;
        $this->load();
    }

    public function id(): string
    {
        return $this->content_op_id ?? '';
    }

    public function data(string $key): string
    {
        return $this->data[$key] ?? '';
    }

    public function update(): void
    {
        if ($this->database->rowExists(NEL_CONTENT_OPS_TABLE, ['content_op_id'], [$this->id()],
            [PDO::PARAM_STR, PDO::PARAM_STR])) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_CONTENT_OPS_TABLE .
                '" SET "content_op_label" = ?, "content_op_url" = ?, "images_only" = ?, "enabled" = ?, "notes" = ?, "moar" = ? WHERE "content_op_id" = ?');
            $this->database->executePrepared($prepared,
                [$this->data('content_op_label'), $this->data('content_op_url'), $this->data('images_only'),
                    $this->data('enabled'), $this->data('notes'), $this->data('moar'), $this->id()]);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_CONTENT_OPS_TABLE .
                '" {"content_op_label". "content_op_url", "images_only", "enabled", "notes", "moar") VALUES (?, ?, ?, ?, ?, ?');
            $this->database->executePrepared($prepared,
                [$this->data('content_op_label'), $this->data('content_op_url'), $this->data('images_only'),
                    $this->data('enabled'), $this->data('notes'), $this->data('moar'), $this->id()]);
        }

        $this->load();
    }

    public function remove(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_CONTENT_OPS_TABLE . '" WHERE "content_op_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function load(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_CONTENT_OPS_TABLE . '" WHERE "content_op_id" = ?');
        $this->data = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);
    }
}
