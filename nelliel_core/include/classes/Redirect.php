<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;
use Nelliel\Content\ContentID;
use Nelliel\Domains\DomainSite;

class Redirect
{
    private static $url = '';
    private static $do_redirect = false;
    private static $delay = 3;

    function __construct()
    {
    }

    public function doRedirect(bool $do_redirect)
    {
        self::$do_redirect = $do_redirect;
    }

    public function URL()
    {
        return self::$url;
    }

    public function changeURL(string $new_url)
    {
        self::$url = $new_url;
    }

    public function go()
    {
        if(self::$do_redirect)
        {
            if(self::$url === '')
            {
                $site_domain = new DomainSite(nel_database());
                self::$url = $site_domain->setting('home_page');
            }

            nel_redirect(self::$url, 5);
        }
    }
}
