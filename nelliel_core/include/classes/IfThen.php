<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

abstract class IfThen
{
    protected $database;
    protected static $if_thens = array();

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function getIfThens(string $board_id): array
    {
        if (!isset(self::$if_thens[$board_id]))
        {
            $this->loadIfThens($board_id);
        }

        return self::$if_thens[$board_id];
    }

    public function processIfThens(string $board_id)
    {
        $if_thens = $this->getIfThens($board_id);

        foreach ($if_thens as $if_then)
        {
            $conditions_met = $this->if($if_then['if']);

            if ($conditions_met)
            {
                $this->then($if_then['then']);
            }
        }
    }

    protected function loadIfThens(string $board_id)
    {
        $prepared = $this->database->prepare(
                'SELECT "if_conditions", "then_actions" FROM "' . NEL_IF_THENS_TABLE . '" WHERE "board_id" = ?');
        $if_thens = $this->database->executePreparedFetchAll($prepared, [$board_id], PDO::FETCH_ASSOC);

        if (!is_array($if_thens))
        {
            self::$if_thens[$board_id] = array();
            return;
        }

        $decoded_sets = array();

        foreach ($if_thens as $if_then)
        {
            $new_set = array();
            $new_set['if'] = json_decode($if_then['if_conditions'], true);
            $new_set['then'] = json_decode($if_then['then_actions'], true);
            $decoded_sets[] = $new_set;
        }

        self::$if_thens[$board_id] = $decoded_sets;
    }

    abstract public function if(array $if): bool;

    abstract public function then(array $if);
}
