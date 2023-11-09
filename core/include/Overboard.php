<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\ContentID;
use Nelliel\Content\Thread;
use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use PDO;

class Overboard
{
    private $database;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    /**
     * Add a thread reference to an overboard.
     */
    public function addThread(Thread $thread, string $overboard_id = null): void
    {
        $add = function ($overboard_id) use ($thread) {
            if (!$this->canInclude($thread, $overboard_id)) {
                return;
            }

            if ($this->threadPresent($thread, $overboard_id)) {
                $this->updateThread($thread, $overboard_id);
            } else {
                $this->insertThread($thread, $overboard_id);
            }
        };

        if (is_null($overboard_id)) {
            $add('sfw');
            $add('all');
        } else {
            $add($overboard_id);
        }

        $this->prune();
    }

    /**
     * Check if a thread is already present on the specified overboard.
     */
    private function threadPresent(Thread $thread, string $overboard_id): bool
    {
        $prepared = $this->database->prepare(
            'SELECT 1 FROM "' . NEL_OVERBOARD_TABLE . '" WHERE "overboard_id" = ? AND "thread_id" = ? AND "board_id" = ?');
        $entry = $this->database->executePreparedFetch($prepared,
            [$overboard_id, $thread->contentID()->threadID(), $thread->domain()->id()], PDO::FETCH_COLUMN);
        return !empty($entry);
    }

    /**
     * Insert a thread reference on the specified overboard.
     */
    private function insertThread(Thread $thread, string $overboard_id): void
    {
        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_OVERBOARD_TABLE .
            '" ("overboard_id", "thread_id", "bump_time", "bump_time_milli", "board_id") VALUES
                    (?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared,
            [$overboard_id, $thread->contentID()->threadID(), $thread->data('bump_time'),
                $thread->data('bump_time_milli'), $thread->domain()->id()]);
    }

    /**
     * Update a thread reference on the specified overboard.
     */
    private function updateThread(Thread $thread, string $overboard_id): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_OVERBOARD_TABLE .
            '" SET "bump_time" = ?, "bump_time_milli" = ? WHERE "overboard_id" = ? AND "thread_id" = ? AND "board_id" = ?');
        $this->database->executePrepared($prepared,
            [$thread->data('bump_time'), $thread->data('bump_time_milli'), $overboard_id,
                $thread->contentID()->threadID(), $thread->domain()->id()]);
    }

    /**
     * Remove a thread reference from the specified overboard.
     */
    public function removeThread(Thread $thread, string $overboard_id = null): void
    {
        if (is_null($overboard_id)) {
            $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_OVERBOARD_TABLE . '" WHERE "thread_id" = ? AND "board_id" = ?');
            $this->database->executePrepared($prepared, [$thread->contentID()->threadID(), $thread->domain()->id()]);
        } else {
            $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_OVERBOARD_TABLE .
                '" WHERE "overboard_id" = ? AND "thread_id" = ? AND "board_id" = ?');
            $this->database->executePrepared($prepared,
                [$overboard_id, $thread->contentID()->threadID(), $thread->domain()->id()]);
        }
    }

    /**
     * Get thread list for the specified overboard.
     */
    public function getThreads(string $overboard_id): array
    {
        $active_threads = array();
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_OVERBOARD_TABLE .
            '" WHERE "overboard_id" = ? ORDER BY "bump_time" DESC, "bump_time_milli" DESC');
        $thread_list = $this->database->executePreparedFetchAll($prepared, [$overboard_id], PDO::FETCH_ASSOC);

        foreach ($thread_list as $thread_data) {
            $content_id = new ContentID(ContentID::createIDString(intval($thread_data['thread_id'])));
            $thread_domain = Domain::getDomainFromID($thread_data['board_id'], $this->database);
            $thread = $content_id->getInstanceFromID($thread_domain);

            if (!$this->canInclude($thread, $overboard_id)) {
                continue;
            }

            $active_threads[] = $thread;
        }

        return $active_threads;
    }

    /**
     * Prune overboard threads.
     */
    public function prune(): void
    {
        $sfw_total = 0;
        $sfw_limit = nel_site_domain()->setting('sfw_overboard_threads');

        foreach ($this->getThreads('sfw') as $thread) {
            if ($sfw_total > $sfw_limit || !$this->canInclude($thread, 'sfw')) {
                $this->removeThread($thread, 'sfw');
                continue;
            }

            $sfw_total ++;
        }

        $all_total = 0;
        $all_limit = nel_site_domain()->setting('overboard_threads');

        foreach ($this->getThreads('all') as $thread) {
            if ($all_total > $all_limit || !$this->canInclude($thread, 'all')) {
                $this->removeThread($thread, 'all');
                continue;
            }

            $all_total ++;
        }
    }

    /**
     * Purge the specified overboard.
     */
    public function purge(string $overboard_id = null): void
    {
        if (is_null($overboard_id)) {
            $this->database->exec('DELETE FROM "' . NEL_OVERBOARD_TABLE . '"');
        } else {
            $prepared = $this->database->prepare('DELETE FROM "' . NEL_OVERBOARD_TABLE . '" WHERE "overboard_id" = ?');
            $this->database->executePrepared($prepared, [$overboard_id]);
        }
    }

    /**
     * Rebuild the specified overboard.
     */
    public function rebuild(string $overboard_id = null): void
    {
        $this->purge($overboard_id);
        $board_ids = $this->database->executeFetchAll('SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"',
            PDO::FETCH_COLUMN);
        $all_overboards = is_null($overboard_id);

        foreach ($board_ids as $board_id) {
            $board = Domain::getDomainFromID($board_id, $this->database);

            if ($all_overboards) {
                foreach ($board->activeThreads(true) as $thread) {
                    $this->addThread($thread, 'sfw');
                    $this->addThread($thread, 'all');
                }
            } else {
                foreach ($board->activeThreads(true) as $thread) {
                    $this->addThread($thread, $overboard_id);
                }
            }
        }

        $this->prune();
    }

    private function canInclude(Thread $thread, string $overboard_id): bool
    {
        if ($overboard_id === 'all') {
            if ($thread->domain()->setting('safety_level') === 'NSFL' &&
                !nel_site_domain()->setting('nsfl_on_overboard')) {
                return false;
            }
        }
        if ($overboard_id === 'sfw') {
            if (!nel_site_domain()->setting('sfw_overboard_active')) {
                return false;
            }

            if ($thread->domain()->setting('safety_level') !== 'SFW') {
                return false;
            }
        }

        return true;
    }
}
