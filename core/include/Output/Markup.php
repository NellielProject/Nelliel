<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Cites;
use Nelliel\Content\Post;

class Markup
{

    function __construct()
    {}

    public function parse(string $text): string
    {
        $modified_text = $this->parseSimple($text);
        $modified_text = $this->parseLines($modified_text);
        $modified_text = $this->parseCallbacks($modified_text);
        return $modified_text;
    }

    public function parsePostComments(string $text, Post $post, bool $dynamic_urls): string
    {
        $modified_text = $this->parse($text);
        $modified_text = $this->parseURLs($modified_text, $post->domain()->setting('url_protocols'));
        $modified_text = $this->parseCites($modified_text, $post, $dynamic_urls);
        return $modified_text;
    }

    public function parseSimple(string $text): string
    {
        $match = '/\|\|(.*?)\|\|/us';
        $replace = '<span class="text-spoiler">$1</span>';

        $modified_text = preg_replace($match, $replace, $text);
        return $modified_text;
    }

    public function parseLines(string $text): string
    {
        $lines = explode("\n", $text);

        if (!is_array($lines)) {
            return $text;
        }

        $modified_lines = array();
        $match = '/^(>(?!>\d+|>>\/[^\/]+\/).*)$/u';
        $replace = '<span class="quote">$1</span>';

        foreach ($lines as $line) {
            // foreach pattern
            $modified_lines[] = preg_replace($match, $replace, $line);
            // end foreach pattern
        }

        return implode("\n", $modified_lines);
    }

    public function parseCallbacks(string $text): string
    {
        return $text;
    }

    public function parseCites(string $text, Post $post, bool $dynamic_urls): string
    {
        $cites = new Cites($post->domain()->database());
        $cite_regex = '/(>>\d+|>>>\/[^\/]+\/\d*)/u';

        $replace_callback = function ($matches) use ($cites, $post, $dynamic_urls) {
            if (!$cites->isCite($matches[0])) {
                return $matches[0];
            }

            $cite_data = $cites->getCiteData($matches[1], $post->domain(), $post->contentID());

            if ($cite_data['exists']) {
                $cite_url = $cites->generateCiteURL($cite_data, $dynamic_urls);
                $link = '<a href="' . $cite_url . '" class="post-cite" data-command="show-linked-post">' . $matches[0] .
                    '</a>';
            } else {
                $link = '<s class="invalid-cite">' . $matches[0] . '</s>';
            }

            return $link;
        };

        return preg_replace_callback($cite_regex, $replace_callback, $text);
    }

    public function parseURLs(string $text, string $protocols): string
    {
        $url_regex = '/(' . $protocols . ')(:\/\/)[^\s]+/';
        $site_domain = nel_site_domain();

        $replace_callback = function ($matches) use ($site_domain) {
            $rel = ($site_domain->setting('nofollow_external_links')) ? 'rel="nofollow"' : '';
            $policy = $site_domain->setting('external_link_referrer_policy');
            $link = '<a href="' . $matches[0] . '" ' . $rel . ' class="external-link" referrerpolicy="' . $policy . '">' .
                $matches[0] . '</a>';
            return $link;
        };

        return preg_replace_callback($url_regex, $replace_callback, $text);
    }
}
