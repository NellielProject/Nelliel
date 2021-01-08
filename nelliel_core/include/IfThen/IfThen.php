<?php

namespace Nelliel\IfThen;

use Nelliel\Domains\Domain;
use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class IfThen
{
    private $domain;
    private $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
    }

    public function loadIfs(string $board_id)
    {
        $prepared = $this->database->prepare(
                'SELECT "if_conditions", "then_actions" FROM "' . NEL_IF_THENS_TABLE . '" WHERE "board_id" = ?');
        $if_thens = $this->database->executePreparedFetchAll($prepared, [$board_id], PDO::FETCH_ASSOC);

        if (!is_array($if_thens))
        {
            return array();
        }

        $decoded_sets = array();

        foreach ($if_thens as $if_then)
        {
            $new_set = array();
            $new_set['if'] = json_decode($if_then['if_conditions'], true);
            $new_set['then'] = json_decode($if_then['then_actions'], true);
            $decoded_sets[] = $new_set;
        }

        return $decoded_sets;
    }

    public function process(array $if_thens)
    {
        foreach ($if_thens as $if_then)
        {
            $total_conditions = count($if_then['if']);
            $conditions_met = 0;

            foreach ($if_then['if'] as $key => $condition)
            {
                // Just test values right now
                switch ($key)
                {
                    case 'name':
                        var_dump('name: ' . $condition);
                        $conditions_met ++;
                        break;
                    case 'email':
                        var_dump('email: ' . $condition);
                        $conditions_met ++;
                        break;
                    case 'subject':
                        var_dump('subject: ' . $condition);
                        $conditions_met ++;
                        break;
                }
            }

            if ($conditions_met !== $total_conditions)
            {
                var_dump("conditions not met");
                continue;
            }

            var_dump("success!");
            // TODO: Process actions
        }
    }
}
