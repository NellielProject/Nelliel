<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentThread;
use Nelliel\Domains\DomainBoard;
use PDO;

class Overboard
{
    private $database;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function addThread(ContentThread $thread): void
    {
        $prepared = $this->database->prepare(
                'SELECT 1 FROM "' . NEL_OVERBOARD_TABLE . '" WHERE "thread_id" = ? AND "board_id" = ?');
        $entry = $this->database->executePreparedFetch($prepared,
                [$thread->contentID()->threadID(), $thread->domain()->id()], PDO::FETCH_COLUMN);

        if (!empty($entry))
        {
            $this->updateThread($thread);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'INSERT INTO "' . NEL_OVERBOARD_TABLE .
                    '" ("thread_id", "last_bump_time", "last_bump_time_milli", "board_id", "sticky") VALUES
                    (?, ?, ?, ?, ?)');
            $this->database->executePrepared($prepared,
                    [$thread->contentID()->threadID(), $thread->data('last_bump_time'),
                        $thread->data('last_bump_time_milli'), $thread->domain()->id(), $thread->data('sticky')]);
        }

        $this->prune();
    }

    public function updateThread(ContentThread $thread): void
    {
        $prepared = $this->database->prepare(
                'UPDATE "' . NEL_OVERBOARD_TABLE .
                '" SET "last_bump_time" = ?, "last_bump_time_milli" = ?, "sticky" = ? WHERE "thread_id" = ? AND "board_id" = ?');
        $this->database->executePrepared($prepared,
                [$thread->data('last_bump_time'), $thread->data('last_bump_time_milli'), $thread->data('sticky'),
                    $thread->contentID()->threadID(), $thread->domain()->id()]);
    }

    public function removeThread(ContentThread $thread): void
    {
        $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_OVERBOARD_TABLE . '" WHERE "thread_id" = ? AND "board_id" = ?');
        $this->database->executePrepared($prepared, [$thread->contentID()->threadID(), $thread->domain()->id()]);
    }

    public function prune(): void
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_OVERBOARD_TABLE . '" ORDER BY "sticky" DESC, "last_bump_time" DESC, "last_bump_time_milli" DESC');
        $thread_list = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);

        if (!is_array($thread_list))
        {
            return;
        }

        $total = 0;
        $limit = nel_site_domain()->setting('overboard_threads');
        $sfw_total = 0;
        $sfw_limit = nel_site_domain()->setting('sfw_overboard_threads');
        $nsfl_total = 0;
        $nsfl_limit = $limit;
        $board_domains = array();

        foreach ($thread_list as $thread)
        {
            if (!isset($board_domains[$thread['board_id']]))
            {
                $board_domains[$thread['board_id']] = new DomainBoard($thread['board_id'], $this->database);
            }

            $board_domain = $board_domains[$thread['board_id']];
            $board_safety_level = $board_domain->setting('safety_level');

            if ($board_safety_level === 'SFW')
            {
                if ($sfw_total === $sfw_limit)
                {
                    $this->removeThread($thread['thread_id'], $thread['board_id']);
                    continue;
                }

                $sfw_total ++;
            }
            else if ($board_safety_level === 'NSFL')
            {
                if ($nsfl_total === $nsfl_limit)
                {
                    $this->removeThread($thread['thread_id'], $thread['board_id']);
                    continue;
                }

                $nsfl_total ++;
            }
            else
            {
                if ($total === $limit)
                {
                    $this->removeThread($thread['thread_id'], $thread['board_id']);
                    continue;
                }

                $total ++;
            }
        }
    }
}
