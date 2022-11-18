<?php
declare(strict_types = 1);

namespace Nelliel\API\JSON;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class BoardsJSON extends JSON
{

    function __construct()
    {}

    protected function generate(): void
    {
        $raw_data = array();
        $board_ids = nel_database('core')->executeFetchAll('SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"',
            PDO::FETCH_COLUMN);

        foreach ($board_ids as $board_id) {
            $board = Domain::getDomainFromID($board_id, nel_database('core'));
            $board_json = new BoardJSON($board);
            $raw_data['boards'][] = $board_json->getRawData();
        }

        $raw_data = nel_plugins()->processHook('nel-in-during-boards-json', [], $raw_data);
        $this->raw_data = $raw_data;
        $this->json = json_encode($raw_data);
        $this->needs_update = false;
    }
}