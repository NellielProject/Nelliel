<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Output\OutputAboutNelliel;

class Start
{

    function __construct()
    {}

    public function startDispatch(): void
    {
        if (nel_visitor_id() === '') {
            nel_visitor_id(true);
        }

        if (isset($_GET['about_nelliel'])) {
            $about_nelliel = new OutputAboutNelliel(nel_site_domain(), false);
            $about_nelliel->render([], false);
            return;
        }

        if (isset($_GET['route'])) {
            $full_uri = $_SERVER['REQUEST_URI'];
            $route_uri = preg_replace('/(.*?)\?route=/u', '', $full_uri);
            $router = new Router($route_uri);
            $router->addRoutes();

            if ($router->dispatch()) {
                return;
            }
        }

        nel_derp(111, __('No valid request was given for processing.'));
    }
}