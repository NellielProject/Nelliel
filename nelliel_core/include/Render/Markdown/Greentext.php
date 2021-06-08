<?php
declare(strict_types = 1);

namespace Nelliel\Render\Markdown;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

trait Greentext
{
    /**
     * Greentext
     * @marker >
     */
    protected function identifyGreentext(string $line, array $lines, int $current): bool
    {
        $greentext_regex = '/^>(?!>\d+|>>\/\w+\/)/iu';
        return preg_match($greentext_regex, $line) === 1;
    }

    protected function consumeGreentext(array $lines, int $current): array
    {
        $block = ['greentext', 'content' => $this->parseInline($lines[$current])];
        return [$block, $current];
    }

    protected function renderGreentext(array $block): string
    {
        return '<span class="greentext">' . $this->renderAbsy($block['content']) . '</span><br>';
    }

    abstract protected function parseInline($text);
    abstract protected function renderAbsy($blocks);
}