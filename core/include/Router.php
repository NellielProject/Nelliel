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

        $this->dispatcher = cachedDispatcher(
            function (RouteCollector $r) use ($site_domain) {
                $r->addGroup('/{domain_id:[^\/]+}/{module:account}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\DispatchAccount';
                        $r->addRoute(['GET', 'POST'], '/{section:login}', $dispatch_class);
                        $r->addRoute(['GET'], '/{section:logout}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:register}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'],
                            '/{section:private-messages}[/{action:[^\/]+}[/{message_id:[^\/]+}]]', $dispatch_class);
                        $r->addRoute(['GET'], '', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:language}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Functions\DispatchLanguage';
                        $r->addRoute(['GET'], '/{section:gettext}[/{action:[^\/]+}]', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:captcha}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Functions\DispatchCAPTCHA';
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
                        $dispatch_class = '\Nelliel\Dispatch\Functions\DispatchNewPost';
                        $r->addRoute(['POST'], '/{section:new-post}[?{query_string:.+}]', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Functions\DispatchThreads';
                        $r->addRoute(['POST'], '/{section:threads}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:snacks}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Functions\DispatchSnacks';
                        $r->addRoute(['POST'], '/{section:user-bans}[/{action:[^\/]+}]', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:blotter}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchBlotter';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:config}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchSiteConfig';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:update}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:config}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchBoardConfig';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:update}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:board-defaults}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchBoardDefaults';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:update}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:permissions}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchPermissions';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:pages}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchPages';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:noticeboard}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchNoticeboard';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:embeds}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchEmbeds';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:file-filters}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchFileFilters';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:filetypes}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchFiletypes';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:filetype-categories}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchFiletypeCategories';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:bans}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchBans';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                    });

                // For now this is ALWAYS last
                $r->addGroup('/{domain_id:[^\/]+}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Functions\DispatchOutput';
                        $r->addRoute(['GET'], '/{page:\d+}[?{query_string:.+}]', $dispatch_class);
                        $r->addRoute(['GET'], '/{section:catalog}/[?{query_string:.+}]', $dispatch_class);
                        // Board subdirectories can be custom so we catch it last and compare in dispatch
                        $r->addRoute(['GET'], '/{section:[^\/]+}/{thread_id:\d+}/{slug:[^\/\?]+}[?{query_string:.+}]',
                            $dispatch_class);
                        $r->addRoute(['GET'], '/[?{query_string:.+}]', $dispatch_class);
                    });
            }, ['cacheFile' => NEL_CACHE_FILES_PATH . 'routes.php']);
    }

    public function dispatch(): bool
    {
        $authorization = new Authorization(nel_database('core'));
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
                $domain = Domain::getDomainFromID($inputs['domain_id'], nel_database('core'));
                $inputs['module'] = $inputs['module'] ?? '';
                $inputs['section'] = $inputs['section'] ?? '';
                $inputs['action'] = $inputs['action'] ?? '';
                parse_str($inputs['query_string'] ?? '', $inputs['parameters']);
                $class = $routeInfo[1];
                $instance = new $class($authorization, $domain, $session);
                $instance->dispatch($inputs);
                return true;
                break;
        }

        return false;
    }
}