<?php
declare(strict_types = 1);

namespace Nelliel\FrontEnd;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NellielPDO;
use PDO;

class Capcode
{
    private $database;
    private $capcode_id = '';
    private $data = array();
    private $front_end_data;

    function __construct(NellielPDO $database, FrontEndData $front_end_data, string $capcode_id)
    {
        $this->database = $database;
        $this->capcode_id = $capcode_id;
        $this->front_end_data = $front_end_data;
        $this->load();
    }

    public function id(): string
    {
        return $this->capcode_id;
    }

    public function data(string $key): string
    {
        return $this->data[$key] ?? '';
    }

    public function update(): void
    {
        if ($this->database->rowExists(NEL_CAPCODES_TABLE, ['capcode_id'], [$this->id()],
            [PDO::PARAM_STR, PDO::PARAM_STR])) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_CAPCODES_TABLE . '" SET "capcode_output" = ?, "moar" = ? WHERE "capcode_id" = ?');
            $this->database->executePrepared($prepared,
                [$this->data('capcode_output'), $this->data('moar'), $this->id()]);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_CAPCODES_TABLE . '" {"capcode_id", "capcode_output", "moar") VALUES (?, ?, ?');
            $this->database->executePrepared($prepared,
                [$this->id(), $this->data('capcode_output'), $this->data('moar')]);
        }

        $this->load();
    }

    public function remove(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_CAPCODES_TABLE . '" WHERE "capcode_id" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function load(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_CAPCODES_TABLE . '" WHERE "capcode_id" = ?');
        $data = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);

        if (is_array($data)) {
            $this->data = $data;
        }
    }
}
