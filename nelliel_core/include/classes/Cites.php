<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;
use Nelliel\Content\ContentPost;
use Nelliel\Content\ContentThread;
use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainBoard;
use PDO;

// TODO: A lot of is is unused and probably not needed
class Cites
{
    private $database;

    function __construct($database)
    {
        $this->database = $database;
    }

    public function getCiteData(string $text, Domain $source_domain, ContentID $source_content_id): array
    {
        $cite_type = $this->citeType($text);

        if ($cite_type['type'] === 'cite')
        {
            $target_domain = $source_domain;
            $target_post = $cite_type['matches'][1];
        }
        else if ($cite_type['type'] === 'cross-cite')
        {
            $target_domain = new DomainBoard($cite_type['matches'][1], $this->database);
            $target_post = $cite_type['matches'][2];
        }
        else
        {
            return array();
        }

        $cite_data = array();
        $cite_data['source_board'] = $source_domain->id();
        $cite_data['source_thread'] = $source_content_id->threadID();
        $cite_data['source_post'] = $source_content_id->postID();
        $cite_data['target_board'] = $target_domain->id();
        $cite_data['target_post'] = $target_post;
        $target_thread = false;

        if ($target_domain->exists())
        {
            $prepared = $this->database->prepare(
                    'SELECT "parent_thread" FROM "' . $target_domain->reference('posts_table') .
                    '" WHERE "post_number" = ?');
            $target_thread = $this->database->executePreparedFetch($prepared, [$target_post], PDO::FETCH_COLUMN);
            $cite_data['target_thread'] = $target_thread;
            $cite_data['exists'] = $target_thread !== false;
        }
        else
        {
            $cite_data['exists'] = false;
        }

        return $cite_data;
    }

    public function getByTarget(string $board_id, string $thread_id, string $post_id, bool $get_all = false)
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_CITES_TABLE .
                '" WHERE "target_board" = ? AND "target_thread" = ? AND "target_post" = ?');

        if ($get_all)
        {
            return $this->database->executePreparedFetchAll($prepared, [$board_id, $thread_id, $post_id],
                    PDO::FETCH_ASSOC);
        }
        else
        {
            return $this->database->executePreparedFetch($prepared, [$board_id, $thread_id, $post_id], PDO::FETCH_ASSOC);
        }
    }

    public function getBySource(string $board_id, string $thread_id, string $post_id, bool $get_all = false)
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_CITES_TABLE . '" WHERE "source_board" = ? AND "source_post" = ?');

        if ($get_all)
        {
            return $this->database->executePreparedFetchAll($prepared, [$board_id, $post_id], PDO::FETCH_ASSOC);
        }
        else
        {
            return $this->database->executePreparedFetch($prepared, [$board_id, $post_id], PDO::FETCH_ASSOC);
        }
    }

    public function updateForPost(ContentPost $post)
    {
        $cite_list_target = $this->getByTarget($post->domain()->id(), $post->contentID()->threadID(),
                $post->contentID()->postID(), true);

        foreach ($cite_list_target as $cite)
        {
            $source_domain = new DomainBoard($cite['source_board'], $this->database);
            $prepared = $this->database->prepare(
                    'UPDATE "' . $source_domain->reference('posts_table') .
                    '" SET "regen_cache" = 1 WHERE "post_number" = ?');
            $this->database->executePrepared($prepared, [$cite['source_post']]);
        }

        $cite_list_source = $this->getBySource($post->domain()->id(), $post->contentID()->threadID(),
                $post->contentID()->postID(), true);

        foreach ($cite_list_source as $cite)
        {
            $source_domain = new DomainBoard($cite['source_board'], $this->database);
            $prepared = $this->database->prepare(
                    'UPDATE "' . $source_domain->reference('posts_table') .
                    '" SET "regen_cache" = 1 WHERE "post_number" = ?');
            $this->database->executePrepared($prepared, [$cite['target_post']]);
        }
    }

    public function updateForThread(ContentThread $thread)
    {
        $cite_list = $this->getByTarget($thread->domain()->id(), $thread->contentID()->threadID(),
                $thread->contentID()->postID(), true);

        foreach ($cite_list as $cite)
        {
            $source_domain = new DomainBoard($cite['source_board'], $this->database);
            $prepared = $this->database->prepare(
                    'UPDATE "' . $source_domain->reference('posts_table') .
                    '" SET "regen_cache" = 1 WHERE "post_number" = ?');
            $this->database->executePrepared($prepared, [$cite['source_post']]);
        }
    }

    public function removeForPost(ContentPost $post)
    {
        $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_CITES_TABLE . '" WHERE "source_board" = ? AND "source_post" = ?');
        $this->database->executePrepared($prepared, [$post->contentID()->threadID(), $post->contentID()->postID()]);
    }

    public function removeForThread(ContentThread $thread)
    {
        $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_CITES_TABLE . '" WHERE "source_board" = ? AND "source_thread" = ?');
        $this->database->executePrepared($prepared, [$thread->contentID()->threadID(), $thread->contentID()->threadID()]);
    }

    public function citeExists(array $cite_data)
    {
        $prepared = $this->database->prepare(
                'SELECT 1 FROM "' . NEL_CITES_TABLE .
                '" WHERE "source_board" = ? AND "source_thread" = ? AND "source_post" = ? AND "target_board" = ? AND "target_thread" = ? AND "target_post" = ?');
        return !empty(
                $this->database->executePreparedFetch($prepared,
                        [$cite_data['source_board'], $cite_data['source_thread'], $cite_data['source_post'],
                            $cite_data['target_board'], $cite_data['target_thread'], $cite_data['target_post']],
                        PDO::FETCH_COLUMN));
    }

    public function addCite(array $cite_data)
    {
        $cite_exists = $this->citeExists($cite_data);

        if ($cite_exists !== false)
        {
            return;
        }

        $prepared = $this->database->prepare(
                'INSERT INTO "' . NEL_CITES_TABLE .
                '" ("source_board", "source_thread", "source_post", "target_board", "target_thread", "target_post") VALUES (?, ?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared,
                [$cite_data['source_board'], $cite_data['source_thread'], $cite_data['source_post'],
                    $cite_data['target_board'], $cite_data['target_thread'], $cite_data['target_post']]);
    }

    public function isCite(string $text)
    {
        return preg_match('/^>>([\d]+)|>>>\/(.+?)\/([\d]+)$/u', $text) === 1;
    }

    public function citeType(string $text)
    {
        $return = array();
        $matches = array();

        if (preg_match('#^>>([\d]+)$#u', $text, $matches) === 1)
        {
            $return['matches'] = $matches;
            $return['type'] = 'cite';
        }
        else if (preg_match('#>>>\/(.+?)\/([\d]+)#u', $text, $matches) === 1)
        {
            $return['matches'] = $matches;
            $return['type'] = 'cross-cite';
        }
        else
        {
            $return['matches'] = $matches;
            $return['type'] = '';
        }

        return $return;
    }

    public function createPostLinkURL(array $cite_data, Domain $source_domain)
    {
        $url = '';

        if (!empty($cite_data))
        {
            $target_domain = new DomainBoard($cite_data['target_board'], $this->database);
            $p_anchor = '#t' . $cite_data['target_thread'] . 'p' . $cite_data['target_post'];
            $url = NEL_BASE_WEB_PATH . $cite_data['target_board'] . '/' . $target_domain->reference('page_dir') . '/' .
                    $cite_data['target_thread'] . '/' .
                    sprintf(nel_site_domain()->setting('thread_filename_format'), $cite_data['target_thread']) . '.html' .
                    $p_anchor;
        }

        return $url;
    }

    public function getCitesFromText(string $text, bool $combine = true): array
    {
        $matches = array();
        preg_match_all('/(>>[\d]+)/', $text, $matches, PREG_PATTERN_ORDER);
        $cites['board'] = $matches[1];
        preg_match_all('/(>>>\/.+?\/[\d]+)/', $text, $matches, PREG_PATTERN_ORDER);
        $cites['crossboard'] = $matches[1];

        if ($combine)
        {
            $cites = array_merge($cites['board'], $cites['crossboard']);
        }

        return $cites;
    }
}
