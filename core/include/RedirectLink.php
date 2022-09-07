<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class RedirectLink
{
    private static $url = '';
    private static $text = '';
    private static $display = false;

    function __construct()
    {}

    public function display(bool $display = null): bool
    {
        if (!is_null($display)) {
            self::$display = $display;
        }

        return self::$display;
    }

    public function URL(): string
    {
        return self::$url;
    }

    public function text(): string
    {
        return self::$text;
    }

    public function changeURL(string $new_url): void
    {
        self::$url = $new_url;
    }

    public function changeText(string $new_text): void
    {
        self::$text = $new_text;
    }
}
