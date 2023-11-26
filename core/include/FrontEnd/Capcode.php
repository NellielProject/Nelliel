<?php
declare(strict_types = 1);

namespace Nelliel\FrontEnd;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;
use Nelliel\Interfaces\MutableData;
use Nelliel\Interfaces\SelfPersisting;
use Nelliel\Tables\TableCapcodes;
use PDO;

class Capcode implements SelfPersisting, MutableData
{
    private NellielPDO $database;
    private string $capcode = '';
    private array $data = array();

    function __construct(NellielPDO $database, string $capcode)
    {
        $this->database = $database;
        $this->capcode = $capcode;
        $this->load();
    }

    public function id(): string
    {
        return $this->capcode;
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
        $this->data[$key] = TableCapcodes::typeCastValue($key, $new_data);
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

    public function save(): void
    {
        if ($this->database->rowExists(NEL_CAPCODES_TABLE, ['capcode'], [$this->id()],
            [PDO::PARAM_STR, PDO::PARAM_STR])) {
            $prepared = $this->database->prepare(
                'UPDATE "' . NEL_CAPCODES_TABLE . '" SET "output" = :output, "moar" = :moar WHERE "capcode" = :capcode');
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_CAPCODES_TABLE . '" {"capcode", "output", "moar") VALUES (:capcode, :output, :moar');
        }

        $prepared->bindValue(':capcode', $this->id() ?? '', PDO::PARAM_STR);
        $prepared->bindValue(':output', $this->getData('output') ?? '', PDO::PARAM_STR);
        $prepared->bindValue(':moar', $this->getData('moar'), PDO::PARAM_STR);
        $this->database->executePrepared($prepared);
    }

    public function delete(): void
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_CAPCODES_TABLE . '" WHERE "capcode" = ?');
        $this->database->executePrepared($prepared, [$this->id()]);
    }
}
