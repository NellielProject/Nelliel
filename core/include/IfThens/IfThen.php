<?php
declare(strict_types = 1);

namespace Nelliel\IfThens;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class IfThen
{
    private $conditions;
    private $actions;
    private $if_thens = array();
    private $loaded = false;

    function __construct(Conditions $conditions, Actions $actions)
    {
        $this->conditions = $conditions;
        $this->actions = $actions;
    }

    public function getIfThens(string $set_id = null): array
    {
        if (!$this->loaded) {
            $this->loadIfThens();
        }

        if (!is_null($set_id)) {
            return $this->if_thens[$set_id] ?? array();
        }

        return $this->if_thens;
    }

    public function process(string $set_id): void
    {
        $if_thens = $this->getIfThens($set_id);

        foreach ($if_thens as $if_then) {
            $conditions = $if_then['conditions'] ?? array();
            $conditions_met = $this->conditions->check($conditions);

            if ($conditions_met) {
                $actions = $if_then['actions'] ?? array();
                $this->actions->do($actions);
            }
        }
    }

    private function loadIfThens(string $board_id = null): void
    {
        $if_thens = array();

        if (file_exists(NEL_CONFIG_FILES_PATH . 'if_thens.php')) {
            include NEL_CONFIG_FILES_PATH . 'if_thens.php';
        }

        if (is_array($if_thens)) {
            $this->if_thens = $if_thens;
            $this->loaded = true;
        }
    }
}
