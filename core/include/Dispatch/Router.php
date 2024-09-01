<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

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
    private $query_string = '';

    function __construct(string $uri)
    {
        $matches = array();
        preg_match('/([^\?]*)\??(.*)/u', $uri, $matches);
        $this->uri = $matches[1] ?? '';
        $this->query_string = $matches[2] ?? '';
    }

    public function addRoutes(): void
    {
        $site_domain = Domain::SITE;

        $this->dispatcher = cachedDispatcher(
            function (RouteCollector $r) use ($site_domain) {
                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:account}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\DispatchAccount';
                        $r->addRoute(['GET', 'POST'], '/{section:login}', $dispatch_class);
                        $r->addRoute(['GET'], '/{section:logout}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:register}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'],
                            '/{section:private-messages}[/{action:[^\/]+}[/{message_id:[^\/]+}]]', $dispatch_class);
                        $r->addRoute(['GET'], '', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:language}',
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
                        $dispatch_class = '\Nelliel\Dispatch\Functions\DispatchRegen';
                        $r->addRoute(['GET'], '/{section:pages}', $dispatch_class);
                        $r->addRoute(['GET'], '/{section:cache}', $dispatch_class);
                        $r->addRoute(['GET'], '/{section:overboard}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Functions\DispatchNewPost';
                        $r->addRoute(['POST'], '/{section:new-post}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:threads}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Functions\DispatchThreads';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{content_id:[^\/]+}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:snacks}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Functions\DispatchSnacks';
                        $r->addRoute(['POST'], '/{section:user-bans}[/{action:[^\/]+}]', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:blotter}',
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

                $r->addGroup('/{domain_id:[^\/]+}/{module:pages}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchPages';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:noticeboard}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchNoticeboard';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:view}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:embeds}',
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

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:filetypes}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchFiletypes';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:filetype-categories}',
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
                        $r->addRoute(['GET', 'POST'], '/{page:\d+}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:manage-boards}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchManageBoards';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:lock|unlock}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:logs}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchLogs';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{log_set:system|public|combined}[/{page:\d+}]', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:news}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchNews';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:image-sets}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchImageSets';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:install|uninstall}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:styles}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchStyles';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:install|uninstall}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:templates}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchTemplates';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:install|uninstall}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:plugins}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchPlugins';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:install|uninstall}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:main-panel}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchMainPanel';
                        $r->addRoute(['GET'], '[/]', $dispatch_class);
                        $r->addRoute(['GET'], '/{section:plugin-controls}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:boardlist}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchBoardlist';
                        $r->addRoute(['GET'], '[/]', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:content-ops}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchContentOps';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:capcodes}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchCapcodes';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:wordfilters}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchWordfilters';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:reports}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchReports';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:dismiss}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:roles}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchRoles';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:users}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchUsers';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:moderation}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Functions\DispatchModeration';
                        $r->addRoute(['GET', 'POST'],
                            '/{section:modmode}/{content_id:[^\/]+}[/{action:ban|ban-delete|delete|delete-by-ip|global-delete-by-ip|lock|unlock|sticky|unsticky|sage|unsage|cyclic|non-cyclic|edit|move|merge|spoiler|unspoiler}]',
                            $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:markup}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchMarkup';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:scripts}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchScripts';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{section:new}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:modify}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:delete}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}/{section:enable|disable}', $dispatch_class);
                    });

                $r->addGroup('/{domain_id:' . $site_domain . '}/{module:ip-info}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchIPInfo';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET'], '/{ip:[^\/]+}/{section:view}', $dispatch_class);
                        $r->addRoute(['POST'], '/{ip:[^\/]+}/{section:add-note}', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{ip:[^\/]+}/{section:delete-note}/{note-id:\d+}',
                            $dispatch_class);
                    });

                $r->addGroup('/{domain_id:[^\/]+}/{module:plugin-controls}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Controls\DispatchPluginControls';
                        $r->addRoute(['GET', 'POST'], '[/]', $dispatch_class);
                        $r->addRoute(['GET', 'POST'], '/{id:[^\/]+}[/{section:[^\/]+}]', $dispatch_class);
                    });

                // For now this is ALWAYS last
                $r->addGroup('/{domain_id:[^\/]+}',
                    function (RouteCollector $r) {
                        $dispatch_class = '\Nelliel\Dispatch\Functions\DispatchOutput';
                        $r->addRoute(['GET'], '/{page:\d+}', $dispatch_class);
                        $r->addRoute(['GET'], '/{section:catalog}/', $dispatch_class);
                        // Board subdirectories can be custom so we catch it last and compare in dispatch
                        $r->addRoute(['GET'], '/{section:[^\/]+}/{thread_id:\d+}/{slug:[^\/\?]+}', $dispatch_class);
                        $r->addRoute(['GET'], '/', $dispatch_class);
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
                $domain = Domain::getDomainFromID($inputs['domain_id'] ?? '');

                if (!$domain->exists()) {
                    nel_derp(80, _('Invalid domain given to router.'));
                }

                $inputs['module'] = $inputs['module'] ?? '';
                $inputs['section'] = $inputs['section'] ?? '';
                $inputs['action'] = $inputs['action'] ?? '';
                parse_str($this->query_string, $inputs['parameters']);
                $class = $routeInfo[1];
                $instance = new $class($authorization, $domain, $session);
                $instance->dispatch($inputs);
                return true;
                break;
        }

        return false;
    }
}