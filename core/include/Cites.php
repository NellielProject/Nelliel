<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\ContentID;
use Nelliel\Content\Post;
use Nelliel\Content\Thread;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use PDO;

class Cites
{
    private $database;

    function __construct($database)
    {
        $this->database = $database;
    }

    public function getCiteData(string $text, Domain $source_domain = null): array
    {
        $matches = array();

        if (preg_match('/^(?:>>|&gt;&gt;)([\d]+)/u', $text, $matches) === 1) {
            $cite_data['type'] = 'post-cite';
            $cite_data['target_post'] = (int) $matches[1];

            if (is_null($source_domain) || $source_domain->id() === Domain::SITE) {
                $cite_data['target_board'] = null;
                $cite_data['exists'] = false;
            } else {
                $cite_data['target_board'] = $source_domain->id();
                $cite_data['exists'] = $source_domain->exists() &&
                    $this->getThreadID($source_domain, $cite_data['target_post']) !== 0;
            }
        } else if (preg_match('/^(?:>>>|&gt;&gt;&gt;)\/(.+?)\/([\d]*)/u', $text, $matches) === 1) {
            $cite_data['target_board'] = $matches[1];
            $target_domain = Domain::getDomainFromID($cite_data['target_board'], $this->database);

            if (!empty($matches[2])) {
                $cite_data['type'] = 'crossboard-post-cite';
                $cite_data['target_post'] = (int) $matches[2];
                $cite_data['exists'] = $target_domain->exists() &&
                    $this->getThreadID($target_domain, $cite_data['target_post']) !== 0;
            } else {
                $cite_data['type'] = 'board-cite';
                $cite_data['target_post'] = 0;
                $cite_data['exists'] = $target_domain->exists();
            }
        } else {
            $cite_data['type'] = 'not-cite';
            $cite_data['exists'] = false;
        }

        return $cite_data;
    }

    public function getForPost(Post $post)
    {
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_CITES_TABLE . '" WHERE "target_board" = ? AND "target_post" = ?');
        $sources = $this->database->executePreparedFetchAll($prepared,
            [$post->domain()->id(), $post->contentID()->postID()], PDO::FETCH_ASSOC);
        $cite_list['sources'] = (is_array($sources)) ? $sources : array();
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . NEL_CITES_TABLE . '" WHERE "source_board" = ? AND "source_post" = ?');
        $targets = $this->database->executePreparedFetchAll($prepared,
            [$post->domain()->id(), $post->contentID()->postID()], PDO::FETCH_ASSOC);
        $cite_list = array();
        $cite_list['sources'] = (is_array($sources)) ? $sources : array();
        $cite_list['targets'] = (is_array($targets)) ? $targets : array();
        return $cite_list;
    }

    public function updateCachesForPost(Post $post): void
    {
        // Update where post targets other posts (backlinks)
        $prepared = $this->database->prepare(
            'SELECT "target_board", "target_post" FROM "' . NEL_CITES_TABLE .
            '" WHERE "source_board" = ? AND "source_post" = ?');
        $cite_list = $this->database->executePreparedFetchAll($prepared,
            [$post->domain()->id(), $post->contentID()->postID()], PDO::FETCH_ASSOC);

        foreach ($cite_list as $cite) {
            $this->setCacheRegenForPost($cite['target_board'], (int) $cite['target_post']);
        }

        // Update where other posts target this post
        $prepared = $this->database->prepare(
            'SELECT "source_board", "source_post" FROM "' . NEL_CITES_TABLE .
            '" WHERE "target_board" = ? AND "target_post" = ?');
        $cite_list = $this->database->executePreparedFetchAll($prepared,
            [$post->domain()->id(), $post->contentID()->postID()], PDO::FETCH_ASSOC);

        foreach ($cite_list as $cite) {
            $this->setCacheRegenForPost($cite['source_board'], (int) $cite['source_post']);
        }
    }

    public function removeForPost(Post $post): void
    {
        $this->updateCachesForPost($post);
        $prepared = $this->database->prepare(
            'DELETE FROM "' . NEL_CITES_TABLE . '" WHERE "source_board" = ? AND ("source_post" = ?)');
        $this->database->executePrepared($prepared, [$post->domain()->id(), $post->contentID()->postID()]);
    }

    private function setCacheRegenForPost(string $domain, int $post_id): void
    {
        $source_domain = Domain::getDomainFromID($domain, $this->database);
        $prepared = $this->database->prepare(
            'UPDATE "' . $source_domain->reference('posts_table') . '" SET "regen_cache" = 1 WHERE "post_number" = ?');
        $this->database->executePrepared($prepared, [$post_id]);
    }

    private function citeStored(array $cite_data, Domain $source_domain, int $source_post): bool
    {
        if ($cite_data['type'] === 'board-cite') {
            $target_domain = new DomainBoard($cite_data['target_board'], $this->database);
            return $target_domain->exists();
        }

        $prepared = $this->database->prepare(
            'SELECT 1 FROM "' . NEL_CITES_TABLE .
            '" WHERE "source_board" = ? AND "source_post" = ? AND "target_board" = ? AND "target_post" = ? LIMIT 1');
        return !empty(
            $this->database->executePreparedFetch($prepared,
                [$source_domain->id(), $source_post, $cite_data['target_board'], $cite_data['target_post']],
                PDO::FETCH_COLUMN));
    }

    public function addCitesFromPost(Post $post): void
    {
        if (nel_true_empty($post->data('comment'))) {
            return;
        }

        $cite_list = $this->getCitesFromText($post->data('comment'));

        foreach ($cite_list as $cite) {
            $cite_data = $this->getCiteData($cite, $post->domain());

            if ($cite_data['exists']) {
                $this->addCite($cite_data, $post->domain(), $post->contentID()->postID());
            }
        }

        $this->updateCachesForPost($post);
    }

    public function addCite(array $cite_data, Domain $source_domain, int $source_post): void
    {
        if ($cite_data['type'] === 'board-cite' || $this->citeStored($cite_data, $source_domain, $source_post)) {
            return;
        }

        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_CITES_TABLE .
            '" ("source_board", "source_post", "target_board", "target_post") VALUES (?, ?, ?, ?)');
        $this->database->executePrepared($prepared,
            [$source_domain->id(), $source_post, $cite_data['target_board'], $cite_data['target_post']]);
    }

    public function isCite(string $text): bool
    {
        return preg_match('/^(>>|&gt;&gt;)([\d]+)|(>>>|&gt;&gt;&gt;)\/(.+?)\/([\d]*)$/u', $text) === 1;
    }

    public function generateCiteURL(array $cite_data, bool $dynamic): string
    {
        $url = '';

        if (!empty($cite_data)) {
            $target_domain = new DomainBoard($cite_data['target_board'], $this->database);

            if ($cite_data['type'] === 'board-cite') {
                $url = NEL_BASE_WEB_PATH . $cite_data['target_board'] . '/';
            } else {
                $target_thread = $this->getThreadID($target_domain, $cite_data['target_post']);
                $content_id = new ContentID(ContentID::createIDString($target_thread, $cite_data['target_post']));
                $post = $content_id->getInstanceFromID($target_domain);
                $url = $post->getURL($dynamic);
            }
        }

        return $url;
    }

    private function getThreadID(Domain $domain, int $post_number): int
    {
        $prepared = $this->database->prepare(
            'SELECT "parent_thread" FROM "' . $domain->reference('posts_table') . '" WHERE "post_number" = ?');
        $parent_thread = $this->database->executePreparedFetch($prepared, [$post_number], PDO::FETCH_COLUMN);
        return $parent_thread !== false ? (int) $parent_thread : 0;
    }

    public function getCitesFromText(string $text, bool $combine = true): array
    {
        $matches = array();
        preg_match_all('/((>>|&gt;&gt;)[\d]+)/', $text, $matches, PREG_PATTERN_ORDER);
        $cites['board'] = $matches[1];
        preg_match_all('/((>>>|&gt;&gt;&gt;)\/.+?\/[\d]+)/', $text, $matches, PREG_PATTERN_ORDER);
        $cites['crossboard'] = $matches[1];

        if ($combine) {
            $cites = array_merge($cites['board'], $cites['crossboard']);
        }

        return $cites;
    }

    public function updateForMovedThread(DomainBoard $old_domain, Thread $moved_thread, array $post_id_conversions): void
    {
        foreach ($moved_thread->getPosts() as $moved_post) {
            $this->updateForMovedPost($old_domain, $moved_post, $post_id_conversions);
        }
    }

    public function updateForMovedPost(DomainBoard $old_domain, Post $moved_post, array $post_id_conversions): void
    {
        $cite_change_callback = function ($matches) use ($post_id_conversions, $old_domain, $moved_post) {
            if (!$this->isCite($matches[0])) {
                return $matches[0];
            }

            // Is a cross-board cite
            if (isset($matches[3])) {
                // Refrences a post in the thread and should be updated
                if (isset($post_id_conversions[$matches[5]])) {
                    return $matches[3] . '/' . $moved_post->domain()->id() . '/' . $post_id_conversions[$matches[5]];
                }

                return $matches[0];
            }

            // Regular cite that references a post in the thread
            if (isset($post_id_conversions[$matches[2]])) {
                return $matches[1] . $post_id_conversions[$matches[2]];
            }

            // Regular cite referencing a post outside the thread (still on the original board)
            $mark = utf8_substr($matches[0], 0, 1) === '>' ? '>' : '&gt;';
            return $matches[1] . $mark . '/' . $old_domain->id() . '/' . $matches[2];
        };

        $moved_post->changeData('comment',
            preg_replace_callback('/(>>|&gt;&gt;)(\d+)|(>>>|&gt;&gt;&gt;)\/(' . $old_domain->id() . ')\/(\d*)/u',
                $cite_change_callback, $moved_post->data('comment')));
        $moved_post->writeToDatabase();
        $this->updateCachesForPost($moved_post);
    }
}
