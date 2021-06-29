<?php
declare(strict_types = 1);

namespace Nelliel\Render\Markdown;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

trait WhitespaceLine
{
    /**
     * WhitespaceLine
     */
    protected function identifyWhitespaceLine(string $line, array $lines, int $current): bool
    {
        return preg_match('/^&.*?\n/ui', $line) === 1;
    }

    protected function consumeWhitespaceLine(array $lines, int $current): array
    {
        $block = ['whitespaceline', 'content' => $this->parseInline($lines[$current])];
        return [$block, $current];
    }

    protected function renderWhitespaceLine(array $block): string
    {
        $content = preg_replace('/^&(.*)\n/ui', "$1", $this->renderAbsy($block['content']));
        return $content;
    }

    abstract protected function parseInline($text);
    abstract protected function renderAbsy($blocks);
}