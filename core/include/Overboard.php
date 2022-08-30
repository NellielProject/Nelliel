<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\Thread;
use Nelliel\Domains\DomainBoard;
use Nelliel\Content\ContentID;
use PDO;

class Overboard
{
    private $database;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    /**
     * Add a thread reference to the overboard.
     */
    public function addThread(Thread $thread): void
    {
        $prepared = $this->database->prepare(
            'SELECT 1 FROM "' . NEL_OVERBOARD_TABLE . '" WHERE "thread_id" = ? AND "board_id" = ?');
        $entry = $this->database->executePreparedFetch($prepared,
            [$thread->contentID()->threadID(), $thread->domain()->id()], PDO::FETCH_COLUMN);

        if (!empty($entry)) {
            $this->updateThread($thread);
        } else {
            $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_OVERBOARD_TABLE .
                '" ("thread_id", "bump_time", "bump_time_milli", "board_id", "sticky") VALUES
                    (?, ?, ?, ?, ?)');
            $this->database->executePrepared($prepared,
                [$thread->contentID()->threadID(), $thread->data('bump_time'), $thread->data('bump_time_milli'),
                    $thread->domain()->id(), (int) $thread->data('sticky')]);
        }

        $this->prune();
    }

    /**
     * Update a thread reference on the overboard.
     */
    public function updateThread(Thread $thread): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_OVERBOARD_TABLE .
            '" SET "bump_time" = ?, "bump_time_milli" = ?, "sticky" = ? WHERE "thread_id" = ? AND "board_id" = ?');
        $this->database->executePrepared($prepared,
            [$thread->data('bump_time'), $thread->data('bump_time_milli'), (int) $thread->data('sticky'),
                $thread->contentID()->threadID(), $thread->domain()->id()]);
    }

    /**
     * Remove a thread reference from the overboard.
     */
    public function removeThread(Thread $thread): void
    {
        $prepared = $this->database->prepare(
            'DELETE FROM "' . NEL_OVERBOARD_TABLE . '" WHERE "thread_id" = ? AND "board_id" = ?');
        $this->database->executePrepared($prepared, [$thread->contentID()->threadID(), $thread->domain()->id()]);
    }

    /**
     * Prune overboard threads.
     */
    public function prune(): void
    {
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_OVERBOARD_TABLE .
            '" ORDER BY "sticky" DESC, "bump_time" DESC, "bump_time_milli" DESC');
        $thread_list = $this->database->executePreparedFetchAll($prepared, null, PDO::FETCH_ASSOC);

        if (!is_array($thread_list)) {
            return;
        }

        $total = 0;
        $limit = nel_site_domain()->setting('overboard_threads');
        $sfw_total = 0;
        $sfw_limit = nel_site_domain()->setting('sfw_overboard_threads');
        $nsfl_total = 0;
        $nsfl_limit = $limit;
        $board_domains = array();

        foreach ($thread_list as $thread_data) {
            if (!isset($board_domains[$thread_data['board_id']])) {
                $board_domains[$thread_data['board_id']] = new DomainBoard($thread_data['board_id'], $this->database);
            }

            $board_domain = $board_domains[$thread_data['board_id']];
            $thread_content_id = new ContentID();
            $thread_content_id->changeThreadID($thread_data['thread_id']);
            $thread = $thread_content_id->getInstanceFromID($board_domain);
            $board_safety_level = $board_domain->setting('safety_level');

            if ($board_safety_level === 'SFW') {
                if ($sfw_total === $sfw_limit) {
                    $this->removeThread($thread);
                    continue;
                }

                $sfw_total ++;
            } else if ($board_safety_level === 'NSFL') {
                if ($nsfl_total === $nsfl_limit) {
                    $this->removeThread($thread);
                    continue;
                }

                $nsfl_total ++;
            } else {
                if ($total === $limit) {
                    $this->removeThread($thread);
                    continue;
                }

                $total ++;
            }
        }
    }
}
