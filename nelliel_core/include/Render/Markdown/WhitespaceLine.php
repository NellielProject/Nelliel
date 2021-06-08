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
    protected function identifyWhitespaceLine($line, $lines, $current): bool
    {
        return preg_match('/^&.*?\n/ui', $line) === 1;
    }

    protected function consumeWhitespaceLine($lines, $current): array
    {
        $block = ['whitespaceline', 'content' => $this->parseInline($lines[$current])];
        return [$block, $current];
    }

    protected function renderWhitespaceLine(array $block): string
    {
        $content = preg_replace('/^&(.*)\n/ui', "$1", $this->renderAbsy($block['content']));
        return '<span class="plaintext">' . $content . '</span><br>';
    }

    abstract protected function parseInline($text);
    abstract protected function renderAbsy($blocks);
}