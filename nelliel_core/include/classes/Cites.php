<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;
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

    private function organizeCiteData(array $cite_data)
    {
        $final_data = array();
        $final_data['source_board'] = $cite_data['source_board'] ?? null;
        $final_data['source_thread'] = $cite_data['source_thread'] ?? null;
        $final_data['source_post'] = $cite_data['source_post'] ?? null;
        $final_data['target_board'] = $cite_data['target_board'] ?? null;
        $final_data['target_thread'] = $cite_data['target_thread'] ?? null;
        $final_data['target_post'] = $cite_data['target_post'] ?? null;
        return $final_data;
    }

    public function getCiteData($source_board, $source_post, $target_board, $target_post)
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_CITES_TABLE .
                '" WHERE "source_board" = ? AND "source_post" = ? AND "target_board" = ? AND "target_post" = ?');
        return $this->database->executePreparedFetch($prepared,
                [$source_board, $source_post, $target_board, $target_post], PDO::FETCH_ASSOC);
    }

    public function getByTarget($board, $post, bool $get_all = false)
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_CITES_TABLE . '" WHERE "target_board" = ? AND "target_post" = ?');

        if ($get_all)
        {
            return $this->database->executePreparedFetchAll($prepared, [$board, $post], PDO::FETCH_ASSOC);
        }
        else
        {
            return $this->database->executePreparedFetch($prepared, [$board, $post], PDO::FETCH_ASSOC);
        }
    }

    public function getBySource($board, $post, bool $get_all = false)
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_CITES_TABLE . '" WHERE "source_board" = ? AND "source_post" = ?');

        if ($get_all)
        {
            return $this->database->executePreparedFetchAll($prepared, [$board, $post], PDO::FETCH_ASSOC);
        }
        else
        {
            return $this->database->executePreparedFetch($prepared, [$board, $post], PDO::FETCH_ASSOC);
        }
    }

    public function citeExists($source_board, $source_post, $target_board, $target_post)
    {
        $prepared = $this->database->prepare(
                'SELECT 1 FROM "' . NEL_CITES_TABLE .
                '" WHERE "source_board" = ? AND "source_post" = ? AND "target_board" = ? AND "target_post" = ?');
        return !empty(
                $this->database->executePreparedFetch($prepared,
                        [$source_board, $source_post, $target_board, $target_post], PDO::FETCH_COLUMN));
    }

    public function addCite(array $data)
    {
        $cite_data = $this->organizeCiteData($data);

        if (!empty(
                $this->getCiteData($cite_data['source_board'], $cite_data['source_post'], $cite_data['target_board'],
                        $cite_data['target_post'])))
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

    public function createPostLinkURL(Domain $domain, ContentID $source_content_id, string $text_input,
            bool $add_cite = false)
    {
        $cite_data = array();
        $matches = array();

        if (preg_match('#^>>([0-9]+)$#', $text_input, $matches) === 1)
        {
            $cite_data = $this->getCiteData($domain->id(), $source_content_id->postID(), $domain->id(), $matches[1]);

            if (empty($cite_data))
            {
                $prepared = $this->database->prepare(
                        'SELECT "parent_thread" FROM "' . $domain->reference('posts_table') . '" WHERE "post_number" = ?');
                $parent_thread = $this->database->executePreparedFetch($prepared, [$matches[1]], PDO::FETCH_COLUMN);

                if (!empty($parent_thread))
                {
                    $cite_data = ['source_board' => $domain->id(), 'source_thread' => $source_content_id->threadID(),
                        'source_post' => $source_content_id->postID(), 'target_board' => $domain->id(),
                        'target_thread' => $parent_thread, 'target_post' => $matches[1]];

                    if ($add_cite)
                    {
                        $this->addCite($cite_data);
                    }
                }
            }
        }
        else if (preg_match('#^>>>\/(.+)\/([0-9]+)$#', $text_input, $matches) === 1)
        {
            $target_domain = new DomainBoard($matches[1], $this->database);
            $cite_data = $this->getCiteData($domain->id(), $source_content_id->postID(), $domain->id(), $matches[2]);

            if (empty($cite_data))
            {
                $prepared = $this->database->prepare(
                        'SELECT "parent_thread" FROM "' . $target_domain->reference('posts_table') .
                        '" WHERE "post_number" = ?');
                $parent_thread = $this->database->executePreparedFetch($prepared, [$matches[2]], PDO::FETCH_COLUMN);

                if (!empty($parent_thread))
                {
                    $cite_data = ['source_board' => $domain->id(), 'source_thread' => $source_content_id->threadID(),
                        'source_post' => $source_content_id->postID(), 'target_board' => $matches[1],
                        'target_thread' => $parent_thread, 'target_post' => $matches[2]];

                    if ($add_cite)
                    {
                        $this->addCite($cite_data);
                    }
                }
            }
        }

        $url = '';

        if (!empty($cite_data))
        {
            $target_domain = new DomainBoard($cite_data['target_board'], $this->database);
            $p_anchor = '#t' . $cite_data['target_thread'] . 'p' . $cite_data['target_post'];
            $url = NEL_BASE_WEB_PATH . $cite_data['target_board'] . '/' . $target_domain->reference('page_dir') . '/' .
                    $cite_data['target_thread'] . '/thread-' . $cite_data['target_thread'] . '.html' . $p_anchor;
        }

        return $url;
    }

    public function removeForThread(Domain $domain, ContentID $content_id)
    {
        $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_CITES_TABLE .
                '" WHERE ("source_board" = ? AND "source_thread" = ?)
                    OR ("target_board" = ? AND "target_thread" = ?)');
        $this->database->executePrepared($prepared,
                [$domain->id(), $content_id->threadID(), $domain->id(), $content_id->threadID()]);
    }

    public function removeForPost(Domain $domain, ContentID $content_id)
    {
        $prepared = $this->database->prepare(
                'DELETE FROM "' . NEL_CITES_TABLE .
                '" WHERE ("source_board" = ? AND "source_thread" = ? AND "source_post" = ?)
                    OR ("target_board" = ? AND "target_thread" = ? AND "target_post" = ?)');
        $this->database->executePrepared($prepared,
                [$domain->id(), $content_id->threadID(), $content_id->postID(), $domain->id(), $content_id->threadID(),
                    $content_id->postID()]);
    }
}
