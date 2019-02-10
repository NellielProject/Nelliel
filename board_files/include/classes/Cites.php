<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class Cites
{
    private $database;
    private $output_filter;

    function __construct($database)
    {
        $this->database = $database;
        $this->output_filter = new OutputFilter();
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

    public function getByTarget($board, $post, bool $get_all = false)
    {
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . CITES_TABLE . '" WHERE "target_board" = ? AND "target_post" = ?');

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
                'SELECT * FROM "' . CITES_TABLE . '" WHERE "source_board" = ? AND "source_post" = ?');

        if ($get_all)
        {
            return $this->database->executePreparedFetchAll($prepared, [$board, $post], PDO::FETCH_ASSOC);
        }
        else
        {
            return $this->database->executePreparedFetch($prepared, [$board, $post], PDO::FETCH_ASSOC);
        }
    }

    public function addCite(array $cite_data)
    {
        $insert_data = $this->organizeCiteData($cite_data);
        $prepared = $this->database->prepare(
                'INSERT INTO "' . CITES_TABLE .
                '" ("source_board", "source_thread", "source_post", "target_board", "target_thread", "target_post") VALUES (?, ?, ?, ?, ?, ?)');
        $this->database->executePrepared($prepared, $insert_data);
    }

    public function generateLink(Domain $domain, $target_element, string $text_input)
    {
        $output_filter = new OutputFilter();
        $raw_segments = preg_split('#(>>[0-9]+)|(>>>\/.+\/[0-9]+)#', $text_input, null, PREG_SPLIT_DELIM_CAPTURE);
        $base_domain = BASE_DOMAIN . BASE_WEB_PATH;

        foreach ($raw_segments as $segment)
        {
            $segment_node = null;

            if (preg_match('#^>>([0-9]+)$#', $segment, $matches) === 1)
            {
                $prepared = $this->database->prepare(
                        'SELECT "parent_thread" FROM "' . $domain->reference('posts_table') . '" WHERE "post_number" = ?');
                $parent_thread = $this->database->executePreparedFetch($prepared, [$matches[1]], PDO::FETCH_COLUMN);

                if (!empty($parent_thread))
                {
                    $p_anchor = '#t' . $parent_thread . 'p' . $matches[1];
                    $url = '//' . $base_domain . $domain->reference('board_directory') . '/' .
                            $domain->reference('page_dir') . '/' . $parent_thread . '/thread-' . $parent_thread . '.html' .
                            $p_anchor;
                    $segment_node = $target_element->ownerDocument->createElement('a', $matches[0]);
                    $segment_node->extSetAttribute('class', 'link-quote');
                    $segment_node->extSetAttribute('data-command', 'show-linked-post');
                    $segment_node->extSetAttribute('href', $url);
                }
            }
            else if (preg_match('#^>>>\/(.+)\/([0-9]+)$#', $segment, $matches) === 1)
            {
                $target_domain = new DomainBoard($matches[1], new CacheHandler(), $this->database);
                $prepared = $this->database->prepare(
                        'SELECT "parent_thread" FROM "' . $target_domain->reference('posts_table') .
                        '" WHERE "post_number" = ?');
                $parent_thread = $this->database->executePreparedFetch($prepared, [$matches[2]], PDO::FETCH_COLUMN);

                if (!empty($parent_thread))
                {
                    $p_anchor = '#t' . $parent_thread . 'p' . $matches[1];
                    $url = '//' . $base_domain . $target_domain->reference('board_directory') . '/' .
                            $target_domain->reference('page_dir') . '/' . $parent_thread . '/thread-' . $parent_thread .
                            '.html' . $p_anchor;
                    $segment_node = $target_element->ownerDocument->createElement('a', $matches[0]);
                    $segment_node->extSetAttribute('class', 'link-quote');
                    $segment_node->extSetAttribute('data-command', 'show-linked-post');
                    $segment_node->extSetAttribute('href', $url);
                }
            }

            if (is_null($segment_node))
            {
                if (preg_match('#^\s*>>#', $segment) === 1)
                {
                    $segment_node = $this->output_filter->postQuote($target_element, $segment);
                }
                else
                {
                    $segment_node = $target_element->ownerDocument->createTextNode($segment);
                }
            }

            $target_element->appendChild($segment_node);
        }
    }
}
