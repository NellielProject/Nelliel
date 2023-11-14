<?php
declare(strict_types = 1);

namespace Nelliel\FrontEnd;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Tables\TableCapcodes;
use PDO;

class Capcode
{
    private $database;
    private $capcode = '';
    private $data = array();
    private $front_end_data;

    function __construct(NellielPDO $database, FrontEndData $front_end_data, string $capcode)
    {
        $this->database = $database;
        $this->capcode = $capcode;
        $this->front_end_data = $front_end_data;
        $this->load();
    }

    public function id(): string
    {
        return $this->capcode;
    }

    public function data(string $key): string
    {
        return $this->data[$key] ?? '';
    }

    public function update(): void
    {
        if ($this->database->rowExists(NEL_CAPCODES_TABLE, ['capcode'], [$this->id()],
            [PDO::PARAM_STR, PDO::PARAM_STR])) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_CAPCODES_TABLE . '" SET "output" = ?, "moar" = ? WHERE "capcode" = ?');
            $this->database->executePrepared($prepared, [$this->data('output'), $this->data('moar'), $this->id()]);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_CAPCODES_TABLE . '" {"capcode", "output", "moar") VALUES (?, ?, ?');
            $this->database->executePrepared($prepared, [$this->id(), $this->data('output'), $this->data('moar')]);
        }

        $this->load();
    }

    public function remove(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_CAPCODES_TABLE . '" WHERE "capcode" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }

    public function load(): void
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . NEL_CAPCODES_TABLE . '" WHERE "capcode" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$this->id()], PDO::FETCH_ASSOC);

        if ($result === false) {
            return;
        }

        $this->data = TableCapcodes::typeCastData($result);
    }
}
