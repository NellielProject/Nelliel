<?php

namespace Nelliel;

use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class OutputFilter
{

    function __construct()
    {
    }

    public function cleanAndEncode(&$string)
    {
        if(empty($string))
        {
            return;
        }

        if (preg_match("#^\s*$#", $string))
        {
            $string = '';
        }

        $string = trim($string);
        $string = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
    }

    public function clearWhitespace(&$string)
    {
        if(empty($string))
        {
            return;
        }

        if (ctype_space($string))
        {
            $string = '';
        }
    }

    public function newlinesToArray($string)
    {
        $text_array = preg_split('#\r\n?|\n#', $string);
        return $text_array;
    }

    public function postQuote($target_element, $text_input)
    {
        if (preg_match('#^\s*>>#', $text_input) === 1)
        {
            $quote_span = $target_element->ownerDocument->createElement('span');
            $quote_span->extSetAttribute('class', 'post-quote');
            $target_element->appendChild($quote_span);
            return $quote_span;
        }

        return false;
    }

    public function postQuoteLink(Domain $domain, $target_element, $text_input)
    {
        $database = nel_database();
        $text_segments = preg_split('#(>>[0-9]+)#', $text_input, null, PREG_SPLIT_DELIM_CAPTURE);
        $base_domain = BASE_DOMAIN . BASE_WEB_PATH;

        foreach ($text_segments as $segment)
        {
            if (preg_match('#^>>([0-9]+)$#', $segment, $matches) === 1)
            {
                $prepared = $database->prepare(
                        'SELECT "parent_thread" FROM "' . $domain->reference('posts_table') .
                        '" WHERE "post_number" = ?');
                $parent_thread = $database->executePreparedFetch($prepared, [$matches[1]], PDO::FETCH_COLUMN);

                if ($parent_thread === false || empty($parent_thread))
                {
                    $segment_node = $target_element->ownerDocument->createTextNode($segment);
                }
                else
                {
                    $p_anchor = '#t' . $parent_thread . 'p' . $matches[1];
                    $url = '//' . $base_domain . '/' . $domain->reference('board_directory') . '/' . $domain->reference('page_dir') . '/' .
                            $parent_thread . '/thread-' . $parent_thread . '.html' . $p_anchor;
                    $segment_node = $target_element->ownerDocument->createElement('a', $matches[0]);
                    $segment_node->extSetAttribute('class', 'link-quote');
                    $segment_node->extSetAttribute('data-command', 'show-linked-post');
                    $segment_node->extSetAttribute('href', $url);
                }
            }
            else
            {
                $segment_node = $target_element->ownerDocument->createTextNode($segment);
            }

            $target_element->appendChild($segment_node);
        }
    }
}