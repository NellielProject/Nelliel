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
    private $domain_id;

    function __construct(string $uri)
    {
        $uri = rawurldecode($uri);
        $matches = array();
        preg_match('/^\/([^\/]+)/u', $uri, $matches);

        if (isset($matches[1]) && Domain::validID($matches[1])) {
            $this->uri = substr($uri, utf8_strlen($matches[0]));
            $this->domain_id = $matches[1];
        } else {
            $this->uri = $uri;
            $this->domain_id = Domain::SITE;
        }
    }

    public function addRoutes(): void
    {
        $this->dispatcher = cachedDispatcher(
            function (RouteCollector $r) {
                $r->addGroup('/{module:account}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\DispatchAccount';
                        $r->addRoute(['GET', 'POST'], '/{section:login}', $dispatch_class);
                        $r->addRoute('GET', '/{section:logout}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:register}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'],
                            '/{section:private-message}[/{action:[^\/]+}[/{message_id:[^\/]+}]]', $dispatch_class);
                    });

                $r->addGroup('/{module:language}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\DispatchLanguage';
                        $r->addRoute(['GET', 'POST'], '/{section:gettext}/{action:[^\/]+}', $dispatch_class);
                    });

                $r->addGroup('/{module:anti-spam}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\DispatchAntiSpam';
                        $r->addRoute(['GET', 'POST'], '/{section:captcha}/{action:[^\/]+}', $dispatch_class);
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
                $domain = Domain::getDomainFromID($inputs['board_id'] ?? $this->domain_id, nel_database());
                $inputs['action'] = $inputs['action'] ?? '';
                $inputs['domain_id'] = $domain->id();
                $dispatch_class = $routeInfo[1];
                $dispatch_instance = new $dispatch_class($authorization, $domain, $session);
                $dispatch_instance->dispatch($inputs);
                return true;
                break;
        }

        return false;
    }
}