<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use \Nelliel\Domain;

function nel_module_dispatch(array $inputs, Domain $domain)
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $inputs = nel_plugins()->processHook('nel-inb4-module-dispatch', [$domain], $inputs);

    switch ($inputs['module'])
    {
        case 'login':
            if (empty($_POST))
            {
                nel_render_login_page($domain);
            }
            else
            {
                $session = new \Nelliel\Session($authorization);
                $session->login();
                nel_render_main_panel($domain, $session->sessionUser());
            }

            break;

        case 'logout':
            $session = new \Nelliel\Session($authorization, true);
            $session->logout();
            break;

        case 'render':
            $inputs['index'] = $_GET['index'] ?? null;
            $inputs['thread'] = $_GET['thread'] ?? null;
            $session = new \Nelliel\Session($authorization, true);

            if(!$session->inModmode($domain->id()))
            {
                $domain->renderActive(true);
            }

            switch ($inputs['action'])
            {
                case 'view-index':
                    require_once INCLUDE_PATH . 'output/main_generation.php';
                    nel_main_thread_generator($domain, 0, false, intval($inputs['index']));
                    break;

                case 'view-thread':
                    require_once INCLUDE_PATH . 'output/thread_generation.php';
                    nel_thread_generator($domain, false, intval($inputs['thread']), $inputs['action']);
                    break;

                case 'expand-thread':
                    require_once INCLUDE_PATH . 'output/thread_generation.php';
                    nel_thread_generator($domain, false, intval($inputs['thread']), $inputs['action']);
                    break;

                case 'collapse-thread':
                    require_once INCLUDE_PATH . 'output/thread_generation.php';
                    nel_thread_generator($domain, false, intval($inputs['thread']), $inputs['action']);
                    break;
            }

            break;

        case 'main-panel':
            $session = new \Nelliel\Session($authorization, true);

            if ($domain->id() !== '')
            {
                nel_render_main_board_panel($domain);
            }
            else
            {
                nel_render_main_panel($domain, $session->sessionUser());
            }

            break;

        case 'users':
            $users_admin = new \Nelliel\Admin\AdminUsers(nel_database(), $authorization, $domain);
            $users_admin->actionDispatch($inputs);
            break;

        case 'roles':
            $roles_admin = new \Nelliel\Admin\AdminRoles(nel_database(), $authorization, $domain);
            $roles_admin->actionDispatch($inputs);
            break;

        case 'site-settings':
            $site_settings_admin = new \Nelliel\Admin\AdminSiteSettings(nel_database(), $authorization, $domain);
            $site_settings_admin->actionDispatch($inputs);
            break;

        case 'manage-boards':
            $create_board_admin = new \Nelliel\Admin\AdminBoards(nel_database(), $authorization, $domain);
            $create_board_admin->actionDispatch($inputs);
            break;

        case 'file-filter':
            $file_filters_admin = new \Nelliel\Admin\AdminFileFilters(nel_database(), $authorization, $domain);
            $file_filters_admin->actionDispatch($inputs);
            break;

        case 'default-board-settings':
            $board_settings_admin = new \Nelliel\Admin\AdminBoardSettings(nel_database(), $authorization, $domain);
            $board_settings_admin->actionDispatch($inputs);
            break;

        case 'language':
            $session = new \Nelliel\Session($authorization, true);

            if ($inputs['action'] === 'extract-gettext')
            {
                $language = new \Nelliel\Language\Language(new \SmallPHPGettext\SmallPHPGettext());
                $language->extractLanguageStrings($session->sessionUser(), LANGUAGES_FILE_PATH . 'extracted/extraction' . date('Y-m-d_H-i-s') . '.pot');
            }

            nel_render_main_panel($domain, $session->sessionUser());
            break;

        case 'reports':
            $reports_admin = new \Nelliel\Admin\AdminReports(nel_database(), $authorization, $domain);
            $reports_admin->actionDispatch($inputs);
            break;

        case 'board-settings':
            $board_settings_admin = new \Nelliel\Admin\AdminBoardSettings(nel_database(), $authorization, $domain);
            $board_settings_admin->actionDispatch($inputs);
            break;

        case 'bans':
            $bans_admin = new \Nelliel\Admin\AdminBans(nel_database(), $authorization, $domain);
            $bans_admin->actionDispatch($inputs);
            break;

        case 'threads-admin':
            $threads_admin = new \Nelliel\Admin\AdminThreads(nel_database(), $authorization, $domain);
            $threads_admin->actionDispatch($inputs);

            if ($inputs['action'] === 'ban-delete')
            {
                $bans_admin = new \Nelliel\Admin\AdminBans(nel_database(), $authorization, $domain);
                $bans_admin->actionDispatch($inputs);
            }

            break;

        case 'threads':
            $content_id = new \Nelliel\ContentID($inputs['content_id']);
            $fgsfds = new \Nelliel\FGSFDS();
            $session = new \Nelliel\Session($authorization);

            if ($inputs['action'] === 'new-post')
            {
                $new_post = new \Nelliel\Post\NewPost(nel_database(), $domain);
                $new_post->processPost();

                if ($fgsfds->getCommand('noko') !== false)
                {
                    if ($session->isActive() && $session->inModmode($inputs['board_id']))
                    {
                        $url_constructor = new \Nelliel\URLConstructor();
                        $url = $url_constructor->dynamic(MAIN_SCRIPT,
                                ['module' => 'render', 'action' => 'view-thread',
                                    'thread' => $fgsfds->getCommandData('noko', 'topic'),
                                    'board_id' => $inputs['board_id']]);

                        nel_redirect($url, 2);
                    }
                    else
                    {
                        $url = $domain->reference('board_directory') . '/' . $domain->reference('page_dir') . '/' .
                                $fgsfds->getCommandData('noko', 'topic') . '/thread-' .
                                $fgsfds->getCommandData('noko', 'topic') . '.html';
                        nel_redirect($url, 2);
                    }
                }
                else
                {
                    if ($session->isActive() && $session->inModmode($inputs['board_id']))
                    {
                        $url_constructor = new \Nelliel\URLConstructor();
                        $url = $url_constructor->dynamic(MAIN_SCRIPT,
                                ['module' => 'render', 'action' => 'view-index', 'index' => '0',
                                    'board_id' => $inputs['board_id']]);

                        nel_redirect($url, 2);
                    }
                    else
                    {
                        $url = $domain->reference('board_directory') . '/' . MAIN_INDEX . PAGE_EXT;
                        nel_redirect($url, 2);
                    }
                }

                nel_clean_exit(false);
            }
            else if ($inputs['action'] === 'delete-post')
            {
                $post = new \Nelliel\Content\ContentPost(nel_database(), $content_id, $domain, true);
                $post->remove();
            }
            else if ($inputs['action'] === 'delete-thread')
            {
                $thread = new \Nelliel\Content\ContentThread(nel_database(), $content_id, $domain, true);
                $thread->remove();
            }
            else if ($inputs['action'] === 'delete-file')
            {
                $file = new \Nelliel\Content\ContentFile(nel_database(), $content_id, $domain, true);
                $file->remove();
            }
            else if ($inputs['action'] === 'ban-file')
            {
                ; // TODO: Add file hash
            }
            else
            {
                if (isset($_POST['form_submit_report']))
                {
                    $reports_admin = new \Nelliel\Admin\AdminReports(nel_database(), $authorization, $domain);
                    $reports_admin->actionDispatch($inputs);

                    if ($session->isActive() && $session->inModmode($inputs['board_id']))
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . MAIN_SCRIPT .
                                '?module=render&action=view-index&index=0&board_id=' . $inputs['board_id'] . '">';
                    }
                    else
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . $domain->reference('board_directory') . '/' .
                                MAIN_INDEX . PAGE_EXT . '">';
                    }
                }

                if (isset($_POST['form_submit_delete']))
                {
                    $thread_handler = new \Nelliel\ThreadHandler(nel_database(), $domain);
                    $thread_handler->processContentDeletes();

                    if ($session->isActive() && $session->inModmode($inputs['board_id']))
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . MAIN_SCRIPT .
                                '?module=render&action=view-index&index=0&board_id=' . $inputs['board_id'] . '">';
                    }
                    else
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . $domain->reference('board_directory') . '/' .
                                MAIN_INDEX . PAGE_EXT . '">';
                    }

                    nel_clean_exit(true, $inputs['board_id']);
                }
            }

            break;

        case 'regen':
            $regen = new \Nelliel\Regen();
            $session = new \Nelliel\Session($authorization, true);
            $user = $session->sessionUser();

            if ($inputs['action'] === 'board-all-pages')
            {
                if (!$user->boardPerm($inputs['board_id'], 'perm_regen_pages'))
                {
                    nel_derp(410, _gettext('You are not allowed to regenerate board pages.'));
                }

                $regen->allPages($domain);
                $archive = new \Nelliel\ArchiveAndPrune(nel_database(), $domain, new \Nelliel\FileHandler());
                $archive->updateThreads();
            }
            else if ($inputs['action'] === 'board-all-caches')
            {
                if (!$user->boardPerm($inputs['board_id'], 'perm_regen_cache'))
                {
                    nel_derp(411, _gettext('You are not allowed to regenerate board caches.'));
                }

                $regen->boardCache($domain);
            }
            else if ($inputs['action'] === 'site-all-caches')
            {
                if (!$user->boardPerm('', 'perm_regen_caches'))
                {
                    nel_derp(412, _gettext('You are not allowed to regenerate site caches.'));
                }

                $regen->siteCache($domain);
            }

            nel_render_main_board_panel($domain);
            break;

        case 'templates':
            $templates_admin = new \Nelliel\Admin\AdminTemplates(nel_database(), $authorization, $domain);
            $templates_admin->actionDispatch($inputs);
            break;

        case 'filetypes':
            $filetypes_admin = new \Nelliel\Admin\AdminFiletypes(nel_database(), $authorization, $domain);
            $filetypes_admin->actionDispatch($inputs);
            break;

        case 'styles':
            $styles_admin = new \Nelliel\Admin\AdminStyles(nel_database(), $authorization, $domain);
            $styles_admin->actionDispatch($inputs);
            break;

        case 'permissions':
            $permissions_admin = new \Nelliel\Admin\AdminPermissions(nel_database(), $authorization, $domain);
            $permissions_admin->actionDispatch($inputs);
            break;

        case 'icon-sets':
            $icon_sets_admin = new \Nelliel\Admin\AdminIconSets(nel_database(), $authorization, $domain);
            $icon_sets_admin->actionDispatch($inputs);
            break;

        case 'news':
            $news_admin = new \Nelliel\Admin\AdminNews(nel_database(), $authorization, $domain);
            $news_admin->actionDispatch($inputs);
            break;

        default:
            break;
    }

    $inputs = nel_plugins()->processHook('nel-in-after-module-dispatch', [$domain], $inputs);
    return $inputs;
}
