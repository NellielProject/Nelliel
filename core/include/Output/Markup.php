<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Cites;
use Nelliel\Content\Post;

class Markup
{
    protected $post;
    protected $dynamic_urls;

    function __construct()
    {}

    public function getMarkupData(string $type): array
    {
        $markup_data = array();

        // TODO: retrieve from settings
        switch ($type) {
            case 'simple':
                $markup_data[] = ['id' => 'ascii-art', 'match' => '/\[aa\](.*?)\[\/aa\]/us',
                    'replace' => '<pre class="ascii-art">$1</pre>'];
                $markup_data[] = ['id' => 'spoiler', 'match' => '/\|\|(.+?)\|\|/us',
                    'replace' => '<span class="markup-spoiler">$1</span>'];
                $markup_data[] = ['id' => 'italic',
                    'match' => "/(?:(?<!'|\*))(?:'|\*){2}(.+?)(?:(?<!'|\*))(?:'|\*){2}(?:(?!'|\*))/us",
                    'replace' => '<span class="markup-italic">$1</span>'];
                $markup_data[] = ['id' => 'bold',
                    'match' => "/(?:(?<!'|\*))(?:'|\*){3}(.+?)(?:(?<!'|\*))(?:'|\*){3}(?:(?!'|\*))/us",
                    'replace' => '<span class="markup-bold">$1</span>'];
                $markup_data[] = ['id' => 'underline', 'match' => '/__(.+?)__/us',
                    'replace' => '<span class="markup-underline">$1</span>'];
                $markup_data[] = ['id' => 'strikethrough', 'match' => '/~~(.+?)~~/us',
                    'replace' => '<span class="markup-strikethrough">$1</span>'];
                break;

            case 'loops':
                $markup_data[] = ['id' => 'nested-spoiler', 'match' => '/\[spoiler (\d+)\](.*?)\[\/spoiler \1\]/us',
                    'replace' => '<span class="markup-spoiler">$2</span>'];
                break;

            case 'lines':
                $markup_data['greentext'] = ['match' => '/^(&gt;(?!&gt;\d+|&gt;&gt;\/[^\/]+\/).*)$/u',
                    'replace' => '<span class="markup-greentext">$1</span>'];
                $markup_data['pinktext'] = ['match' => '/^(&lt;.*)$/u',
                    'replace' => '<span class="markup-pinktext">$1</span>'];
                $markup_data['orangetext'] = ['match' => '/^(\^.*)$/u',
                    'replace' => '<span class="markup-orangetext">$1</span>'];
                break;

            case 'callbacks':

                break;

            case 'blocks':
                $markup_data[] = ['id' => 'ascii-art', 'match' => '/\[aa\]|\[\/aa\]/',
                    'replace' => '<pre class="ascii-art">$1</pre>'];
                break;
        }

        return $markup_data;
    }

    public function parsePostComments(string $text, Post $post, bool $dynamic_urls): string
    {
        $this->post = $post;
        $this->dynamic_urls = $dynamic_urls;
        $modified_text = $text;
        $modified_text = $this->parseBlocks($modified_text);
        return $modified_text;
    }

    public function parseInline(string $text): string
    {
        $modified_text = $text;
        $modified_text = $this->parseSimple($modified_text);
        $modified_text = $this->parseLoops($modified_text);
        $modified_text = $this->parseCallbacks($modified_text);
        $modified_text = $this->parseURLs($modified_text);
        $modified_text = $this->parseCites($modified_text);
        return $modified_text;
    }

    public function parseBlocks(string $text, array $markup_data = null, $recursive_call = false): string
    {
        if (is_null($markup_data)) {
            $markup_data = $this->getMarkupData('blocks');
        }

        $modified_text = $text;
        $modified_blocks = array();

        foreach ($markup_data as $data) {
            $blocks = preg_split($data['match'], $modified_text);

            // If error or only one block, there's no block markup left to parse
            if (!is_array($blocks) || count($blocks) === 1) {
                continue;
            }

            $block_count = count($blocks);

            for ($i = 1; $i <= $block_count; $i ++) {
                $block = $blocks[$i - 1];

                // Even numbered blocks will be regex matches
                if ($i % 2 === 0) {
                    $modified = preg_replace('/^(.*)$/us', $data['replace'], $block);
                } else {
                    $modified = $this->parseBlocks($block, $markup_data, true);
                }

                $modified_blocks[] = $modified;
            }

            // Make sure we don't duplicate everything if this is the top level loop
            if (!$recursive_call) {
                break;
            }
        }

        if (!empty($modified_blocks)) {
            $modified_text = implode('', $modified_blocks);
        } else {
            $modified_text = $this->parseLines($modified_text);
            $modified_text = $this->parseInline($modified_text);
        }

        return $modified_text;
    }

    public function parseLines(string $text, array $markup_data = null): string
    {
        if (is_null($markup_data)) {
            $markup_data = $this->getMarkupData('lines');
        }

        $lines = explode("\n", $text);

        if (!is_array($lines)) {
            return $text;
        }

        $modified_lines = array();

        foreach ($lines as $line) {
            foreach ($markup_data as $data) {
                $line = preg_replace($data['match'], $data['replace'], $line);
            }

            $modified_lines[] = $line;
        }

        return implode("\n", $modified_lines);
    }

    public function parseSimple(string $text, array $markup_data = null): string
    {
        if (is_null($markup_data)) {
            $markup_data = $this->getMarkupData('simple');
        }

        $modified_text = $text;

        foreach ($markup_data as $data) {
            $modified_text = preg_replace($data['match'], $data['replace'], $modified_text);
        }

        return $modified_text;
    }

    public function parseLoops(string $text, array $markup_data = null): string
    {
        if (is_null($markup_data)) {
            $markup_data = $this->getMarkupData('loops');
        }

        $modified_text = $text;

        foreach ($markup_data as $data) {
            do {
                $compare = $modified_text;
                $modified_text = preg_replace($data['match'], $data['replace'], $modified_text);
            } while ($compare !== $modified_text);
        }

        return $modified_text;
    }

    public function parseCallbacks(string $text, array $markup_data = null): string
    {
        if (is_null($markup_data)) {
            $markup_data = $this->getMarkupData('callbacks');
        }

        $modified_text = $text;

        foreach ($markup_data as $data) {
            $modified_text = preg_replace_callback($data['match'], $data['replace'], $modified_text);
        }

        return $modified_text;
    }

    public function parseCites(string $text, Post $post = null, bool $dynamic_urls = null): string
    {
        if (is_null($post)) {
            $post = $this->post;
        }

        if (is_null($dynamic_urls)) {
            $dynamic_urls = $this->dynamic_urls;
        }

        $cites = new Cites($post->domain()->database());
        $cite_regex = '/((?:>>|&gt;&gt;)\d+|(?:>>>|&gt;&gt;&gt;)\/[^\/]+\/\d*)/u';

        $replace_callback = function ($matches) use ($cites, $post, $dynamic_urls) {
            if (!$cites->isCite($matches[0])) {
                return $matches[0];
            }

            $cite_data = $cites->getCiteData($matches[1], $post->domain(), $post->contentID());

            if ($cite_data['exists']) {
                $cite_url = $cites->generateCiteURL($cite_data, $dynamic_urls);

                if ($cite_data['type'] === 'board-cite') {
                    $link = '<a href="' . $cite_url . '" class="board-cite">' . $matches[0] . '</a>';
                } else {
                    $link = '<a href="' . $cite_url . '" class="post-cite" data-command="show-linked-post">' .
                        $matches[0] . '</a>';
                }
            } else {
                $link = '<span class="invalid-cite">' . $matches[0] . '</span>';
            }

            return $link;
        };

        return preg_replace_callback($cite_regex, $replace_callback, $text);
    }

    public function parseURLs(string $text, string $protocols = null): string
    {
        if (is_null($protocols)) {
            $protocols = $this->post->domain()->setting('url_protocols');
        }

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
