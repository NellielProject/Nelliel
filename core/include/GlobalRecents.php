<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\ContentID;
use Nelliel\Content\Post;
use Nelliel\Database\NellielPDO;
use Nelliel\Domains\Domain;
use PDO;

class GlobalRecents
{
    private $database;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    /**
     * Add a post reference to recents.
     */
    public function addPost(Post $post): void
    {
        $add = function ($post) {
            if ($this->postPresent($post)) {
                $this->updatePost($post);
            } else {
                $this->insertPost($post);
            }
        };

        $add($post);
        $this->prune();
    }

    /**
     * Check if a post is already present in global recents.
     */
    private function postPresent(Post $post): bool
    {
        $prepared = $this->database->prepare(
            'SELECT 1 FROM "' . NEL_GLOBAL_RECENTS_TABLE . '" WHERE "content_id" = ? AND "board_id" = ?');
        $entry = $this->database->executePreparedFetch($prepared,
            [$post->contentID()->getIDString(), $post->domain()->id()], PDO::FETCH_COLUMN);
        return !empty($entry);
    }

    /**
     * Insert a post reference in global recents.
     */
    private function insertPost(Post $post): void
    {
        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_GLOBAL_RECENTS_TABLE .
            '" ("content_id", "post_time", "post_time_milli", "board_id") VALUES
                    (?, ?, ?, ?)');
        $this->database->executePrepared($prepared,
            [$post->contentID()->getIDString(), $post->data('post_time'), $post->data('post_time_milli'),
                $post->domain()->id()]);
    }

    /**
     * Update a post reference in global recents.
     */
    private function updatePost(Post $post): void
    {
        $prepared = $this->database->prepare(
            'UPDATE "' . NEL_GLOBAL_RECENTS_TABLE .
            '" SET "post_time" = ?, "post_time_milli" = ?, WHERE "content_id" = ? AND "board_id" = ?');
        $this->database->executePrepared($prepared,
            [$post->data('post_time'), $post->data('post_time_milli'), $post->contentID()->getIDString(),
                $post->domain()->id()]);
    }

    /**
     * Remove a post reference from global recents.
     */
    public function removePost(Post $post): void
    {
        $prepared = $this->database->prepare(
            'DELETE FROM "' . NEL_GLOBAL_RECENTS_TABLE . '" WHERE "content_id" = ? AND "board_id" = ?');
        $this->database->executePrepared($prepared, [$post->contentID()->getIDString(), $post->domain()->id()]);
    }

    /**
     * Get list of recent posts.
     */
    public function getPosts(int $limit = 0, array $safety_level_exclusions = array()): array
    {
        $recent_posts = array();

        if ($limit <= 0) {
            $limit = nel_site_domain()->setting('max_recent_posts');
        }

        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_GLOBAL_RECENTS_TABLE . '" ORDER BY "post_time" DESC, "post_time_milli" DESC LIMIT ?');
        $post_list = $this->database->executePreparedFetchAll($prepared, [$limit], PDO::FETCH_ASSOC);

        foreach ($post_list as $post_data) {
            $thread_domain = Domain::getDomainFromID($post_data['board_id'], $this->database);

            if (in_array($thread_domain->setting('safety_level'), $safety_level_exclusions)) {
                continue;
            }

            $content_id = new ContentID($post_data['content_id']);
            $post = $content_id->getInstanceFromID($thread_domain);
            $recent_posts[] = $post;
        }

        return $recent_posts;
    }

    /**
     * Prune recent posts.
     */
    public function prune(): void
    {
        $limit = nel_site_domain()->setting('max_recent_posts');
        $total = 0;

        foreach ($this->getPosts() as $post) {
            if ($total > $limit) {
                $this->removePost($post);
                continue;
            }

            $total ++;
        }
    }

    /**
     * Purge all recent posts.
     */
    public function purge(): void
    {
        $this->database->exec('DELETE FROM "' . NEL_GLOBAL_RECENTS_TABLE . '"');
    }

    /**
     * Rebuild recent posts.
     */
    public function rebuild(): void
    {
        $this->purge();
        $board_ids = $this->database->executeFetchAll('SELECT "board_id" FROM "' . NEL_BOARD_DATA_TABLE . '"',
            PDO::FETCH_COLUMN);
        $limit = nel_site_domain()->setting('max_recent_posts');

        foreach ($board_ids as $board_id) {
            $board = Domain::getDomainFromID($board_id, $this->database);

            foreach ($board->recentPosts($limit) as $post) {
                $this->addPost($post);
            }
        }

        $this->prune();
    }
}
