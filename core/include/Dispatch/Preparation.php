<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\DNSBL;
use Nelliel\Router;
use Nelliel\Output\OutputAboutNelliel;

class Preparation
{

    function __construct()
    {}

    public function prepare()
    {
        if (nel_visitor_id() === '') {
            nel_visitor_id(true);
        }

        if (empty($_GET) && empty($_POST)) {
            return;
        }

        if (isset($_GET['special'])) {
            nel_special();
        }

        if (isset($_GET['about_nelliel'])) {
            $about_nelliel = new OutputAboutNelliel(nel_site_domain(), false);
            $about_nelliel->render([], false);
            nel_clean_exit();
        }

        if (isset($_GET['blank']) || isset($_GET['tpilb'])) {
            nel_tpilb();
        }

        $dnsbl = new DNSBL(nel_database('core'));
        $dnsbl->checkIP(nel_request_ip_address());

        if (isset($_GET['route'])) {
            $router = new Router($_GET['route'] ?? '');
            $router->addRoutes();

            if ($router->dispatch()) {
                return;
            }
        }
    }
}