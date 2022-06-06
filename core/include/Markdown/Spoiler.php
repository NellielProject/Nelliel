<?php
declare(strict_types = 1);

namespace Nelliel\Markdown;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

trait Spoiler
{

    /**
     * Parses text spoilers.
     *
     * @marker ||
     */
    protected function parseSpoiler($markdown)
    {
        $matches = array();

        if (preg_match('/\|\|(.*)\|\|/', $markdown, $matches) === 1) {
            return [['spoiler', $this->parseInline($matches[1])], utf8_strlen($matches[0])];
        }

        return [['text', $markdown[0] . $markdown[1]], 2];
    }

    protected function renderSpoiler($block)
    {
        return '<span class="text-spoiler">' . $this->renderAbsy($block[1]) . '</span>';
    }

    abstract protected function parseInline($text);

    abstract protected function renderAbsy($blocks);
}
