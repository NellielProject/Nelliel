<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\DomainSite;

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
        if (self::$do_redirect) {
            if (self::$url === '') {
                $site_domain = new DomainSite(nel_database('core'));
                self::$url = $site_domain->reference('home_page');
            }

            nel_redirect(self::$url, self::$delay);
        }
    }
}
