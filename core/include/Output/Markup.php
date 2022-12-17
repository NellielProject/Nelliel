<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Cites;
use Nelliel\Content\Post;
use Nelliel\Database\NellielPDO;
use PDO;

class Markup
{
    private $database;
    private $post;
    private $dynamic_urls;
    private $markup_data;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function getMarkupData(string $type): array
    {
        if (is_null($this->markup_data)) {
            $markup_list = $this->database->executeFetchAll('SELECT * FROM "' . NEL_MARKUP_TABLE . '"', PDO::FETCH_ASSOC);
            foreach ($markup_list as $markup) {
                $this->markup_data[$markup['type']][$markup['label']] = ['match' => $markup['match'],
                    'replace' => $markup['replace']];
            }
        }

        return $this->markup_data[$type] ?? array();
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
        $modified_text = $this->parseURLs($modified_text);
        $modified_text = $this->parseCites($modified_text);
        return $modified_text;
    }

    public function parseBlocks(string $text, array $markup_data = array(), $recursive_call = false): string
    {
        if (empty($markup_data)) {
            $markup_data = $this->getMarkupData('block');
        }

        $markup_data = nel_plugins()->processHook('nel-in-before-markup-blocks', [$text], $markup_data);
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

    public function parseLines(string $text, array $markup_data = array()): string
    {
        if (empty($markup_data)) {
            $markup_data = $this->getMarkupData('line');
        }

        $lines = explode("\n", $text);
        $markup_data = nel_plugins()->processHook('nel-in-before-markup-lines', [$lines], $markup_data);

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

    public function parseSimple(string $text, array $markup_data = array()): string
    {
        if (empty($markup_data)) {
            $markup_data = $this->getMarkupData('simple');
        }

        $markup_data = nel_plugins()->processHook('nel-in-before-markup-simple', [$text], $markup_data);
        $modified_text = $text;

        foreach ($markup_data as $data) {
            $modified_text = preg_replace($data['match'], $data['replace'], $modified_text);
        }

        return $modified_text;
    }

    public function parseLoops(string $text, array $markup_data = array()): string
    {
        if (empty($markup_data)) {
            $markup_data = $this->getMarkupData('loop');
        }

        $markup_data = nel_plugins()->processHook('nel-in-before-markup-loops', [$text], $markup_data);
        $modified_text = $text;

        foreach ($markup_data as $data) {
            do {
                $compare = $modified_text;
                $modified_text = preg_replace($data['match'], $data['replace'], $modified_text);
            } while ($compare !== $modified_text);
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
