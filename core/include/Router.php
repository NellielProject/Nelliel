<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\cachedDispatcher;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class Router
{
    private $dispatcher;
    private $uri = '';
    private $routes = array();

    function __construct(string $uri)
    {
        $this->uri = rawurldecode($uri);
    }

    //
    public function addRoutes(): void
    {
        $site_domain = Domain::SITE;
        // {domain_id:[^\/]+}

        $this->dispatcher = cachedDispatcher(
            function (RouteCollector $r) use ($site_domain) {
                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:account}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\DispatchAccount';
                        $r->addRoute(['GET', 'POST'], '/{section:login}', $dispatch_class);
                        $r->addRoute('GET', '/{section:logout}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:register}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'],
                            '/{section:private-messages}[/{action:[^\/]+}[/{message_id:[^\/]+}]]', $dispatch_class);
                        $r->addRoute(['GET'], '', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:language}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\DispatchLanguage';
                        $r->addRoute(['GET'], '/{section:extract-gettext}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:captcha}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\DispatchCAPTCHA';
                        $r->addRoute(['GET'], '/{section:get}', $dispatch_class);
                        $r->addRoute(['GET'], '/{section:regenerate}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:banners}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\DispatchBanners';
                        $r->addRoute(['GET'], '/{section:random}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:regen}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\DispatchRegen';
                        $r->addRoute(['GET'], '/{section:pages}', $dispatch_class);
                        $r->addRoute(['GET'], '/{section:cache}', $dispatch_class);
                        $r->addRoute(['GET'], '/{section:overboard}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\DispatchOutput';
                        $r->addRoute(['GET'], '/{page:\d+}[/{parameters:.+}]', $dispatch_class);
                        $r->addRoute(['GET'], '/{section:catalog}/[{parameters:.+}]', $dispatch_class);
                        // Board subdirectories can be custom so we catch it last and compare in dispatch
                        $r->addRoute(['GET'], '/{section:[^\/]+}/{thread_id:\d+}/{slug:[^\/]+}[/{parameters:.+}]',
                            $dispatch_class);
                        $r->addRoute(['GET'], '/[{parameters:.+}]', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\DispatchNewPost';
                        $r->addRoute(['POST'], '/{section:new-post}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\DispatchThreads';
                        $r->addRoute(['POST'], '/{section:threads}', $dispatch_class);
                    });
            }, ['cacheFile' => NEL_CACHE_FILES_PATH . 'route.php']);
    }

    public function dispatch(): bool
    {
        $authorization = new Authorization(nel_database());
        $session = new Session();
        $routeInfo = $this->dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $this->uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                break;

            case Dispatcher::FOUND:
                $inputs = $routeInfo[2];
                $inputs['method'] = $_SERVER['REQUEST_METHOD'];
                $domain = Domain::getDomainFromID($inputs['domain_id'], nel_database());
                $inputs['module'] = $inputs['module'] ?? '';
                $inputs['section'] = $inputs['section'] ?? '';
                $inputs['action'] = $inputs['action'] ?? '';
                $class = $routeInfo[1];
                $instance = new $class($authorization, $domain, $session);
                $instance->dispatch($inputs);
                return true;
                break;
        }

        return false;
    }
}