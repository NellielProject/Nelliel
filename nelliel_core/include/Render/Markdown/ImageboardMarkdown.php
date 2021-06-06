<?php
declare(strict_types = 1);

namespace Nelliel\Render\Markdown;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use cebe\markdown\Parser;
use ReflectionMethod;

class ImageboardMarkdown extends Parser
{
    use \cebe\markdown\inline\StrikeoutTrait;
    protected $domain;

    function __construct(Domain $domain, string $input = null)
    {
        $this->domain = $domain;

        if (!is_null($input))
        {
            $this->addFromString($input);
        }
    }

    // Custom method needed as protocols are variable and can't be done in annotation form
    protected function inlineMarkers()
    {
        $markers = [];
        // detect "parse" functions
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PROTECTED) as $method)
        {
            $methodName = $method->getName();
            $matches = array();

            if ($method->getDocComment() !== false && strncmp($methodName, 'parse', 5) === 0)
            {
                preg_match_all('/@marker ([^\s]+)/', $method->getDocComment(), $matches);

                foreach ($matches[1] as $match)
                {
                    $markers[$match] = $methodName;
                }
            }
        }

        $protocols_list = explode('|', $this->domain->setting('url_protocols'));

        if (is_array($protocols_list))
        {
            foreach ($protocols_list as $protocol)
            {
                $markers[$protocol] = 'parseURL';
            }
        }

        //$markers['>>'] = 'parseCite'; // remove this and just use annotation (since we probably can do that)?
        return $markers;
    }

    protected function consumeParagraph($lines, $current)
    {
        // consume until newline
        $content = [];
        for ($i = $current, $count = count($lines); $i < $count; $i ++)
        {
            // We want to preserve empty lines so that check was removed
            $content[] = $lines[$i];
        }
        $block = ['paragraph', 'content' => $this->parseInline(implode("\n", $content))];
        return [$block, -- $i];
    }

    // Comment will only be a single paragraph so we leave that to the template
    protected function renderParagraph($block)
    {
        return $this->renderAbsy($block['content']);
    }

    // Only need a standard <br> for newlines
    protected function renderText($text)
    {
        return str_replace("\n", '<br>', $text[1]);
    }

    protected function parseURL($text): array
    {
        $url_protocols = $this->domain->setting('url_protocols');
        $url_regex = '#(' . $url_protocols . ')(:\/\/)[^\s]+#';
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

    protected function renderURL($block): string
    {
        $rel = (nel_site_domain()->setting('noreferrer_nofollow')) ? 'rel="noreferrer nofollow"' : '';
        $open_tag = '<a href="' . $block[1] . '" ' . $rel . ' class="external-link">';
        return $open_tag . $block[1] . '</a>';
    }
}
