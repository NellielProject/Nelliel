<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

class Redirect
{
    private static $url = '';
    private static $do_redirect = false;
    private static $delay = 3;

    function __construct()
    {}

    public function doRedirect(bool $do_redirect = null): bool
    {
        if (!is_null($do_redirect)) {
            self::$do_redirect = $do_redirect;
        }

        return self::$do_redirect;
    }

    public function URL(string $new_url = null): string
    {
        if (!is_null($new_url)) {
            self::$url = $new_url;
        }

        return self::$url;
    }

    public function delay(int $new_delay = null): int
    {
        if (!is_null($new_delay)) {
            self::$delay = $new_delay;
        }

        return self::$delay;
    }

    public function go(): void
    {
        if (self::$do_redirect) {
            if (self::$url === '') {
                self::$url = nel_site_domain()->reference('home_page');
            }

            $redirect = '<meta http-equiv="refresh" content="' . self::$delay . ';URL=' . self::$url . '">';
            echo $redirect;
        }
    }
}
