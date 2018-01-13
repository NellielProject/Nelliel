<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_encode_and_clean_output(&$string)
{
    if ($string === '' || preg_match("#^\s*$#", $string))
    {
        return '';
    }

    if (get_magic_quotes_gpc())
    {
        $string = stripslashes($string);
    }

    $string = trim($string);
    $string = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
}

function nel_clear_whitespace($string)
{
    if (ctype_space($string))
    {
        return '';
    }
}

function nel_newlines_to_array($input)
{
    $text_array = preg_split('#\r\n?|\n#', $input);
    return $text_array;
}

function nel_post_quote($target_element, $text_input)
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

function nel_post_quote_link($target_element, $text_input)
{
    $dbh = nel_database();
    $text_segments = preg_split('#(>>[0-9]+)#', $text_input, null, PREG_SPLIT_DELIM_CAPTURE);

    foreach ($text_segments as $segment)
    {
        if(preg_match('#^>>([0-9]+)$#', $segment, $matches) === 1)
        {
            $prepared = $dbh->prepare('SELECT "parent_thread" FROM "' . POST_TABLE . '" WHERE "post_number" = ? LIMIT 1');
            $parent_thread = $dbh->executePreparedFetch($prepared, array($matches[1]), PDO::FETCH_COLUMN);

            if ($parent_thread === false || empty($parent_thread))
            {
                $segment_node = $target_element->ownerDocument->createTextNode($segment);
            }
            else
            {
                $p_anchor = '#p' . $parent_thread. '_' . $matches[1];
                $segment_node= $target_element->ownerDocument->createElement('a', $matches[0]);
                $segment_node->extSetAttribute('class', 'link-quote');
                $segment_node->extSetAttribute('href', PAGE_DIR . $parent_thread . '/' . $parent_thread . '.html' . $p_anchor, 'none');
            }
        }
        else
        {
            $segment_node = $target_element->ownerDocument->createTextNode($segment);
        }

        $target_element->appendChild($segment_node);
    }
}