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

    public function getForPost(ContentPost $post)
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

    public function getForThread(ContentThread $thread)
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_CITES_TABLE . '" WHERE "target_board" = ? AND "target_thread" = ?');
        $sources = $this->database->executePreparedFetchAll($prepared,
                [$thread->domain()->id(), $thread->contentID()->threadID()], PDO::FETCH_ASSOC);
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_CITES_TABLE . '" WHERE "source_board" = ? AND "source_thread" = ?');
        $targets = $this->database->executePreparedFetchAll($prepared,
                [$thread->domain()->id(), $thread->contentID()->threadID()], PDO::FETCH_ASSOC);
        $cite_list = array();
        $cite_list['sources'] = (is_array($sources)) ? $sources : array();
        $cite_list['targets'] = (is_array($targets)) ? $targets : array();
        return $cite_list;
    }

    public function updateForPost(ContentPost $post)
    {
        // Update where post targets other posts (backlinks)
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_CITES_TABLE . '" WHERE "source_board" = ? AND "source_post" = ?');
        $cite_list = $this->database->executePreparedFetchAll($prepared,
                [$post->domain()->id(), $post->contentID()->threadID()], PDO::FETCH_ASSOC);

        foreach ($cite_list as $cite)
        {
            $source_domain = new DomainBoard($cite['target_board'], $this->database);
            $prepared = $this->database->prepare(
                    'UPDATE "' . $source_domain->reference('posts_table') .
                    '" SET "regen_cache" = 1 WHERE "post_number" = ?');
            $this->database->executePrepared($prepared, [$cite['target_post']]);
        }

        // Update where other posts target this post
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_CITES_TABLE . '" WHERE "target_board" = ? AND "target_post" = ?');
        $cite_list = $this->database->executePreparedFetchAll($prepared,
                [$post->domain()->id(), $post->contentID()->threadID()], PDO::FETCH_ASSOC);

        foreach ($cite_list as $cite)
        {
            $source_domain = new DomainBoard($cite['source_board'], $this->database);
            $prepared = $this->database->prepare(
                    'UPDATE "' . $source_domain->reference('posts_table') .
                    '" SET "regen_cache" = 1 WHERE "post_number" = ?');
            $this->database->executePrepared($prepared, [$cite['source_post']]);
        }
    }

    public function updateForThread(ContentThread $thread)
    {
        // Update where posts in this thread target other posts (backlinks)
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_CITES_TABLE . '" WHERE "source_board" = ? AND "source_thread" = ?');
        $cite_list = $this->database->executePreparedFetchAll($prepared,
                [$thread->domain()->id(), $thread->contentID()->threadID()], PDO::FETCH_ASSOC);

        foreach ($cite_list as $cite)
        {
            $source_domain = new DomainBoard($cite['target_board'], $this->database);
            $prepared = $this->database->prepare(
                    'UPDATE "' . $source_domain->reference('posts_table') .
                    '" SET "regen_cache" = 1 WHERE "post_number" = ?');
            $this->database->executePrepared($prepared, [$cite['target_post']]);
        }

        // Update where other posts target posts in this thread
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_CITES_TABLE . '" WHERE "target_board" = ? AND "target_thread" = ?');
        $cite_list = $this->database->executePreparedFetchAll($prepared,
                [$thread->domain()->id(), $thread->contentID()->threadID()], PDO::FETCH_ASSOC);

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
                'DELETE FROM "' . NEL_CITES_TABLE . '" WHERE "source_board" = ? AND ("source_post" = ? OR "target_post" = ?)');
        $this->database->executePrepared($prepared, [$post->domain()->id(), $post->contentID()->postID(), $post->contentID()->postID()]);
    }

    public function removeForThread(ContentThread $thread)
    {
        $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_CITES_TABLE . '" WHERE "source_board" = ? AND ("source_thread" = ? OR "target_thread" = ?)');
        $this->database->executePrepared($prepared, [$thread->domain()->id(), $thread->contentID()->threadID(), $thread->contentID()->threadID()]);
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
