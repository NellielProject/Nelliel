<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Cites;
use Nelliel\Content\Post;
use Nelliel\Database\NellielPDO;
use PDO;
use Nelliel\Domains\Domain;

class Markup
{
    private $database;
    private $domain;
    private $dynamic_urls = false;
    private $markup_data;
    private $protocols = '';

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function getMarkupData(string $type = null, bool $only_enabled = true): array
    {
        if (is_null($this->markup_data)) {
            if ($only_enabled) {
                $markup_list = $this->database->executeFetchAll(
                    'SELECT * FROM "' . NEL_MARKUP_TABLE . '" WHERE "enabled" = 1', PDO::FETCH_ASSOC);
            } else {
                $markup_list = $this->database->executeFetchAll('SELECT * FROM "' . NEL_MARKUP_TABLE . '"',
                    PDO::FETCH_ASSOC);
            }

            foreach ($markup_list as $markup) {
                $this->markup_data[$markup['type']][$markup['label']] = ['match' => $markup['match_regex'],
                    'replace' => $markup['replacement'], 'enabled' => intval($markup['enabled'])];
            }
        }

        if (is_null($type)) {
            return $this->markup_data;
        }

        return $this->markup_data[$type] ?? array();
    }

    public function parseText(string $text, Domain $source_domain = null, bool $dynamic_urls = false): string
    {
        $this->domain = $source_domain;
        $this->dynamic_urls = $dynamic_urls;
        $this->protocols = (!is_null($source_domain)) ? $source_domain->setting('url_protocols') ?? '': '';
        $modified_text = $text;
        $modified_text = $this->parseBlocks($modified_text);
        return $modified_text;
    }

    public function parsePostComments(string $text, Post $post, bool $dynamic_urls = false): string
    {
        $this->domain = $post->domain();
        $this->dynamic_urls = $dynamic_urls;
        $this->protocols = $post->domain()->setting('url_protocols') ?? '';
        $modified_text = $text;
        $modified_text = $this->parseBlocks($modified_text);
        return $modified_text;
    }

    public function parseInline(string $text): string
    {
        $modified_text = $text;
        $modified_text = $this->parseSimple($modified_text);
        $modified_text = $this->parseLoops($modified_text);
        $modified_text = $this->parseURLs($modified_text, $this->protocols);
        $modified_text = $this->parseCites($modified_text, $this->domain, $this->dynamic_urls);
        return $modified_text;
    }

    public function parseBlocks(string $text, array $markup_data = array(), $recursive_call = false): string
    {
        $markup_data = empty($markup_data) ? $this->getMarkupData() : $markup_data;
        $block_markup = $markup_data['block'];
        $block_markup = nel_plugins()->processHook('nel-inb4-markup-blocks', [$text], $block_markup);

        $modified_text = $text;
        $modified_blocks = array();

        foreach ($block_markup as $data) {
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
        $markup_data = empty($markup_data) ? $this->getMarkupData() : $markup_data;
        $line_markup = $markup_data['line'];
        $lines = explode("\n", $text);
        $line_markup = nel_plugins()->processHook('nel-inb4-markup-lines', [$lines], $line_markup);

        if (!is_array($lines)) {
            return $text;
        }

        $modified_lines = array();

        foreach ($lines as $line) {
            foreach ($line_markup as $data) {
                $line = preg_replace($data['match'], $data['replace'], $line);
            }

            $modified_lines[] = $line;
        }

        return implode('', $modified_lines);
    }

    public function parseSimple(string $text, array $markup_data = array()): string
    {
        $markup_data = empty($markup_data) ? $this->getMarkupData() : $markup_data;
        $simple_markup = $markup_data['simple'];
        $simple_markup = nel_plugins()->processHook('nel-inb4-markup-simple', [$text], $simple_markup);

        $modified_text = $text;

        foreach ($simple_markup as $data) {
            $modified_text = preg_replace($data['match'], $data['replace'], $modified_text);
        }

        return $modified_text;
    }

    public function parseLoops(string $text, array $markup_data = array()): string
    {
        $markup_data = empty($markup_data) ? $this->getMarkupData() : $markup_data;
        $loop_markup = $markup_data['loop'];
        $loop_markup = nel_plugins()->processHook('nel-inb4-markup-loops', [$text], $loop_markup);

        $modified_text = $text;

        foreach ($loop_markup as $data) {
            do {
                $compare = $modified_text;
                $modified_text = preg_replace($data['match'], $data['replace'], $modified_text);
            } while ($compare !== $modified_text);
        }

        return $modified_text;
    }

    public function parseCites(string $text, Domain $source_domain = null, bool $dynamic_urls = false): string
    {
        $cites = new Cites($this->database);
        $cite_regex = '/((?:>>|&gt;&gt;)\d+|(?:>>>|&gt;&gt;&gt;)\/[^\/]+\/\d*)/u';

        $replace_callback = function ($matches) use ($cites, $source_domain, $dynamic_urls) {
            if (!$cites->isCite($matches[0])) {
                return $matches[0];
            }

            $cite_data = $cites->getCiteData($matches[1], $source_domain);

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

    public function parseURLs(string $text, string $protocols): string
    {
        $url_regex = '/(' . $protocols . ')(:\/\/)[^\s]+/';
        $site_domain = nel_get_cached_domain(Domain::SITE);

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
