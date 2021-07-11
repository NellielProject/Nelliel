<?php
declare(strict_types = 1);

namespace Nelliel\IfThens;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\NellielPDO;

class IfThen
{
    private $database;
    private $conditions;
    private $actions;
    private $if_thens = array();
    private $loaded = false;

    function __construct(NellielPDO $database, Conditions $conditions, Actions $actions)
    {
        $this->database = $database;
        $this->conditions = $conditions;
        $this->actions = $actions;
    }

    public function getIfThens(): array
    {
        if (!$this->loaded)
        {
            $this->loadIfThens();
        }

        return $this->if_thens;
    }

    public function process()
    {
        $if_thens = $this->getIfThens();

        foreach ($if_thens as $if_then)
        {
            $conditions = $if_then['conditions'] ?? array();
            $conditions_met = $this->conditions->check($conditions);

            if ($conditions_met)
            {
                $actions = $if_then['actions'] ?? array();
                $this->actions->do($actions);
            }
        }
    }

    private function loadIfThens(string $board_id = null)
    {
        include NEL_CONFIG_FILES_PATH . 'if_thens.php';

        if (is_array($if_thens))
        {
            $this->if_thens = $if_thens;
            $this->loaded = true;
        }
    }
}
