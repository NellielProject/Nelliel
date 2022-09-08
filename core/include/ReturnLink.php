<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class ReturnLink
{
    private static $url = '';
    private static $text = '';

    function __construct(string $url = null, string $text = null)
    {
        $this->URL($url);
        $this->text($text);
    }

    public function ready(): bool
    {
        return self::$url !== '' && self::$text !== '';
    }

    public function URL(string $new_url = null): string
    {
        if (!is_null($new_url)) {
            self::$url = $new_url;
        }

        return self::$url;
    }

    public function text(string $new_text = null): string
    {
        if (!is_null($new_text)) {
            self::$text = $new_text;
        }

        return self::$text;
    }
}
