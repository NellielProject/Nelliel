<?php
declare(strict_types = 1);

namespace Nelliel\Render\Markdown;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

trait URL
{

    /**
     * URLs
     */
    protected function parseURL(string $text): array
    {
        // The parent class must define the url_protocols variable
        $url_regex = '#(' . $this->url_protocols . ')(:\/\/)[^\s]+#';
        $matches = array();

        if (preg_match($url_regex, $text, $matches) === 1)
        {
            // Provides the suffix for the render function (i.e. render<suffix>)
            // Also provides string length as an offset so this isn't reparsed
            return [['url', $matches[0]], utf8_strlen($matches[0])];
        }
        else
        {
            return [['text', substr($text, 0, 3)], 4]; // needs to be better
        }
    }

    protected function renderURL(array $block): string
    {
        $rel = (nel_site_domain()->setting('noreferrer_nofollow')) ? 'rel="noreferrer nofollow"' : '';
        $open_tag = '<a href="' . $block[1] . '" ' . $rel . ' class="external-link">';
        return $open_tag . $block[1] . '</a>';
    }

    abstract protected function parseInline($text);

    abstract protected function renderAbsy($blocks);
}