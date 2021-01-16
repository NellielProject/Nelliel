<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\DomainBoard;
use PDO;

class UpdateOverboard
{
    private $database;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function addThread(string $thread_id, string $board_id)
    {
        $board_domain = new DomainBoard($board_id, nel_database());
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . $board_domain->reference('threads_table') . '" WHERE "thread_id" = ?');
        $thread_data = $this->database->executePreparedFetch($prepared, [$thread_id], PDO::FETCH_ASSOC);
        $prepared = $this->database->prepare(
                'SELECT "entry" FROM "' . NEL_OVERBOARD_TABLE . '" WHERE "thread_id" = ? AND "board_id" = ?');
        $entry = $this->database->executePreparedFetch($prepared, [$thread_id, $board_id], PDO::FETCH_COLUMN);

        if (!empty($entry))
        {
            $prepared = $this->database->prepare(
                    'UPDATE "' . NEL_OVERBOARD_TABLE .
                    '" SET "last_bump_time" = ?, "last_bump_time_milli" = ? WHERE "entry" = ?');
            $this->database->executePrepared($prepared,
                    [$thread_data['last_bump_time'], $thread_data['last_bump_time_milli'], $entry]);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . NEL_OVERBOARD_TABLE .
                    '" ("thread_id", "last_bump_time", "last_bump_time_milli", "board_id") VALUES
                    (?, ?, ?, ?)');
            $this->database->executePrepared($prepared,
                    [$thread_id, $thread_data['last_bump_time'], $thread_data['last_bump_time_milli'], $board_id]);
        }
    }
}
