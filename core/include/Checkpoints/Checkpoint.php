<?php
declare(strict_types = 1);

namespace Nelliel\Checkpoints;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class Checkpoint
{
    private $conditions;
    private $actions;
    private $checkpoints = array();
    private $loaded = false;

    function __construct(Conditions $conditions, Actions $actions)
    {
        $this->conditions = $conditions;
        $this->actions = $actions;
    }

    public function get(string $set_id = null): array
    {
        if (!$this->loaded) {
            $this->load();
        }

        if (!is_null($set_id)) {
            return $this->checkpoints[$set_id] ?? array();
        }

        return $this->checkpoints;
    }

    public function process(string $set_id): void
    {
        $checkpoints = $this->get($set_id);

        foreach ($checkpoints as $checkpoint) {
            $conditions = $checkpoint['conditions'] ?? array();
            $conditions_met = $this->conditions->check($conditions);

            if ($conditions_met) {
                $actions = $checkpoint['actions'] ?? array();
                $this->actions->do($actions);
            }
        }
    }

    private function load(string $board_id = null): void
    {
        $checkpoints = array();

        if (file_exists(NEL_CONFIG_FILES_PATH . 'checkpoints.php')) {
            include NEL_CONFIG_FILES_PATH . 'checkpoints.php';
        }

        if (is_array($checkpoints)) {
            $this->checkpoints = $checkpoints;
            $this->loaded = true;
        }
    }
}
