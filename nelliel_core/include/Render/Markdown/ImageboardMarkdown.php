<?php
declare(strict_types = 1);

namespace Nelliel\Render\Markdown;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use cebe\markdown\Parser;
use cebe\markdown\inline\StrikeoutTrait;
use ReflectionMethod;

class ImageboardMarkdown extends Parser
{
    use StrikeoutTrait;
    use Greentext;
    use WhitespaceLine;
    use URL;
    protected $domain;
    protected $url_protocols;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->url_protocols = $this->domain->setting('url_protocols');
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
}
