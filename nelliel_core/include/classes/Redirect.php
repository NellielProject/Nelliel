<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\DomainSite;

class Redirect
{
    private static $url = '';
    private static $do_redirect = false;
    private static $delay = 3;

    function __construct()
    {
    }

    public function doRedirect(bool $do_redirect): void
    {
        self::$do_redirect = $do_redirect;
    }

    public function URL(): string
    {
        return self::$url;
    }

    public function changeURL(string $new_url): void
    {
        self::$url = $new_url;
    }

    public function changeDelay(int $new_delay): void
    {
        self::$delay = $new_delay;
    }

    public function go(): void
    {
        if (self::$do_redirect)
        {
            if (self::$url === '')
            {
                $site_domain = new DomainSite(nel_database());
                self::$url = $site_domain->setting('home_page');
            }

            nel_redirect(self::$url, self::$delay);
        }
    }
}
