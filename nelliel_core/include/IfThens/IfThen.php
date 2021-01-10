<?php

namespace Nelliel\IfThens;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\NellielPDO;
use PDO;

class IfThen
{
    private $database;
    private $conditions;
    private $actions;
    private static $if_thens = array();

    function __construct(NellielPDO $database, Conditions $conditions, Actions $actions)
    {
        $this->database = $database;
        $this->conditions = $conditions;
        $this->actions = $actions;
    }

    public function getIfThens(string $board_id): array
    {
        if (!isset(self::$if_thens[$board_id]))
        {
            $this->loadIfThens($board_id);
        }

        return self::$if_thens[$board_id];
    }

    public function process(string $board_id)
    {
        $if_thens = $this->getIfThens($board_id);

        foreach ($if_thens as $if_then)
        {
            $conditions_met = $this->conditions->check($if_then['if']);

            if ($conditions_met)
            {
                $this->actions->do($if_then['then']);
            }
        }
    }

    private function loadIfThens(string $board_id)
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
}
