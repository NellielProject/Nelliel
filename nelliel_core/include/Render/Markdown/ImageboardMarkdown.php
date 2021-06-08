<?php
declare(strict_types = 1);

namespace Nelliel\Render\Markdown;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use cebe\markdown\Parser;
use cebe\markdown\inline\StrikeoutTrait;
use ReflectionMethod;
use Nelliel\Cites;

class ImageboardMarkdown extends Parser
{
    use StrikeoutTrait;
    use Greentext;
    use WhitespaceLine;
    use URL;
    protected $domain;
    protected $url_protocols;
    protected $cites;
    protected $post_content_id;

    function __construct(Domain $domain, ContentID $post_content_id)
    {
        $this->domain = $domain;
        $this->url_protocols = $this->domain->setting('url_protocols');
        $this->cites = new Cites($domain->database());
        $this->post_content_id = $post_content_id;
    }

    public function parse($text)
    {
        $this->prepare();

        if (ltrim($text) === '')
        {
            return '';
        }

        $text = str_replace(["\r\n", "\n\r", "\r"], "\n", $text);

        $this->prepareMarkers($text);
        $blocks = explode("\n", $text);

        // We have to enclose empty and whitespace lines so the parser doesn't purge them
        // Newlines were removed from user input during the explode function
        // So we can use those to identify internal modifications
        for ($i = 0; $i < count($blocks); $i ++)
        {
            if (trim($blocks[$i]) === '')
            {
                $blocks[$i] = '&' . $blocks[$i] . "\n";
            }
        }

        $absy = $this->parseBlocks($blocks);
        $markup = $this->renderAbsy($absy);

        $this->cleanup();
        return $markup;
    }

    protected function consumeParagraph($lines, $current)
    {
        // consume until newline
        $content = [];
        for ($i = $current, $count = count($lines); $i < $count; $i ++)
        {
            $line = $lines[$i];

            // Adapted from GithubMarkdown
            // Without this only some blocks will be parsed before it collapses the remaining lines
            // Don't ask how long I spent losing sanity before realizing what 'break paragraphs' meant
            if ($line === '' || ltrim($lines[$i]) === '' || $this->identifyGreentext($line, $lines, $i) ||
                    $this->identifyWhitespaceLine($line, $lines, $i))
            {
                break;
            }
            else
            {
                $content[] = $line;
            }
        }

        $block = ['paragraph', 'content' => $this->parseInline(implode("\n", $content))];
        return [$block, -- $i];
    }

    // Use line breaks instead of p tags
    protected function renderParagraph($block)
    {
        return '<span class="plaintext">' . $this->renderAbsy($block['content']) . '</span><br>';
    }

    protected function inlineMarkers()
    {
        $markers = [];
        // detect "parse" functions
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PROTECTED) as $method)
        {
            $methodName = $method->getName();
            $matches = array();

            // Extra check needed since we're in strict mode
            if ($method->getDocComment() !== false && strncmp($methodName, 'parse', 5) === 0)
            {
                preg_match_all('/@marker ([^\s]+)/', $method->getDocComment(), $matches);

                foreach ($matches[1] as $match)
                {
                    $markers[$match] = $methodName;
                }
            }
        }

        // Available protocols depend on a dynamic external setting so markers can't be collected from annotations
        $protocols_list = explode('|', $this->url_protocols);

        if (is_array($protocols_list))
        {
            foreach ($protocols_list as $protocol)
            {
                $markers[$protocol] = 'parseURL';
            }
        }

        return $markers;
    }

    /**
     * ContentCite
     * @marker >>
     * @marker >>>
     */
    protected function parseContentCite(string $text): array
    {
        $cite_type = $this->cites->citeType($text);

        if ($cite_type['type'] !== 'not-cite')
        {
            return [['contentcite', $cite_type['matches'][0]], utf8_strlen($cite_type['matches'][0])];
        }
        else
        {
            return [['text', '>>'], 2];
        }
    }

    protected function renderContentCite(array $block): string
    {
        $cite_data = $this->cites->getCiteData($block[1], $this->domain, $this->post_content_id);

        if (isset($cite_data['exists']) && $cite_data['exists'])
        {
            $cite_url = $this->cites->createPostLinkURL($cite_data, $this->domain);
            $this->cites->addCite($cite_data);
            return '<a href="' . $cite_url . '" class="post-cite" data-command="show-linked-post">' . $block[1] . '</a>';
        }
        else
        {
            return '<s class="invalid-cite">' . $block[1] . '</s>';
        }
    }
}
