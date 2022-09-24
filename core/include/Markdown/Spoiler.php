<?php
declare(strict_types = 1);

namespace Nelliel\Markdown;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

trait Spoiler
{

    protected function identifySpoiler($line)
    {
        return preg_match('/\|\|/', $line) === 1;
    }

    protected function consumeSpoiler($lines, $current)
    {
        $processed = array();
        $in_spoiler = false;

        foreach ($lines as $line) {
            if ($line !== '@' . "\n") {
                $line .= "\n";
            }

            $sub_lines = explode('||', $line);
            $sub_line_count = count($sub_lines);

            for ($i = 0; $i < $sub_line_count; $i ++) {
                $entry = array();

                if (!$in_spoiler) {
                    if ($i > 0 && $sub_line_count > 1) {
                        $in_spoiler = true;
                        $entry['type'] = 'spoiler_start';
                        $entry['content'] = $this->parseBlocks([$sub_lines[$i]]);
                    } else {
                        continue;
                    }
                } else {
                    if ($i > 0 && $sub_line_count > 1) {
                        $in_spoiler = false;
                        $entry['type'] = 'spoiler_end';
                        $entry['content'] = $this->parseBlocks([$sub_lines[$i]]);
                    } else {
                        $entry['type'] = 'normal';
                        $entry['content'] = $this->parseBlocks([$sub_lines[$i]]);
                    }
                }

                $processed[] = $entry;
            }
        }

        $block = ['spoiler', $processed];
        return [$block, count($lines)];
    }

    protected function renderSpoiler($block)
    {
        $line = '';

        foreach ($block[1] as $entry) {

            if ($entry['type'] === 'spoiler_start') {
                $line .= '<span class="text-spoiler">';
            }

            if ($entry['type'] === 'spoiler_end') {
                $line .= '</span>';
            }

            $line .= $this->renderAbsy($entry['content']);
        }

        return $line;
    }

    abstract protected function parseBlocks($lines);

    abstract protected function renderAbsy($absy);
}
