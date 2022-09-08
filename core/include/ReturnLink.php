<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class ReturnLink
{
    private $url = '';
    private $text = '';

    function __construct(string $url = null, string $text = null)
    {
        $this->URL($url);
        $this->text($text);
    }

    public function ready(): bool
    {
        return $this->url !== '' && $this->text !== '';
    }

    public function URL(string $new_url = null): string
    {
        if (!is_null($new_url)) {
            $this->url = $new_url;
        }

        return $this->url;
    }

    public function text(string $new_text = null): string
    {
        if (!is_null($new_text)) {
            $this->text = $new_text;
        }

        return $this->text;
    }
}
