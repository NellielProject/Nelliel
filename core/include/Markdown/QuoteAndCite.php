<?php
declare(strict_types = 1);

namespace Nelliel\Markdown;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

trait QuoteAndCite
{

    /**
     * Parses quotes and content cites.
     *
     * @marker >
     */
    protected function parseQuoteAndContentCite(string $text): array
    {
        $quote_regex = '/^>(?:(?!>\d+|>>\/\w+\/))(.*)/iu';
        $matches = array();

        if (preg_match($quote_regex, $text, $matches) === 1) {
            return [['quote', $this->parseInline($matches[1])], utf8_strlen($matches[0])];
        } else {
            $cite_type = $this->cites->citeType($text);

            if ($cite_type['type'] !== 'not-cite') {
                return [['contentcite', $cite_type['matches'][0]], utf8_strlen($cite_type['matches'][0])];
            }

            return [['text', '>'], 1];
        }
    }

    protected function renderQuote(array $block): string
    {
        return '<span class="quote">>' . $this->renderAbsy($block[1]) . '</span>';
    }

    protected function renderContentCite(array $block): string
    {
        $cite_data = $this->cites->getCiteData($block[1], $this->domain, $this->post_content_id);

        if (isset($cite_data['exists']) && $cite_data['exists']) {
            $cite_url = $this->cites->createPostLinkURL($cite_data, $this->domain, $this->dynamic);
            return '<a href="' . $cite_url . '" class="post-cite" data-command="show-linked-post">' . $block[1] . '</a>';
        } else {
            return '<s class="invalid-cite">' . $block[1] . '</s>';
        }
    }
}
