<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_module_dispatch($inputs, $domain)
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $inputs = nel_plugins()->processHook('nel-inb4-module-dispatch', array(), $inputs);

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
            $session = new \Nelliel\Session($authorization, true);

            switch ($inputs['action'])
            {
                case 'view-index':
                    require_once INCLUDE_PATH . 'output/main_generation.php';
                    nel_main_thread_generator($domain, 0, false, intval($inputs['section']));
                    break;

                case 'view-thread':
                    require_once INCLUDE_PATH . 'output/thread_generation.php';
                    nel_thread_generator($domain, false, intval($inputs['section']));
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

        case 'staff':
            $staff_panel = new \Nelliel\Admin\AdminStaff(nel_database(), $authorization, $domain);
            $staff_panel->actionDispatch($inputs);
            break;

        case 'site-settings':
            $site_settings_panel = new \Nelliel\Admin\AdminSiteSettings(nel_database(), $authorization, $domain);
            $site_settings_panel->actionDispatch($inputs);
            break;

        case 'manage-boards':
            $create_board_panel = new \Nelliel\Admin\AdminManageBoards(nel_database(), $authorization, $domain);
            $create_board_panel->actionDispatch($inputs);
            break;

        case 'file-filter':
            $file_filters_panel = new \Nelliel\Admin\AdminFileFilters(nel_database(), $authorization, $domain);
            $file_filters_panel->actionDispatch($inputs);
            break;

        case 'default-board-settings':
            $board_settings_panel = new \Nelliel\Admin\AdminBoardSettings(nel_database(), $authorization, $domain);
            $board_settings_panel->actionDispatch($inputs);
            break;

        case 'language':
            $session = new \Nelliel\Session($authorization, true);

            if ($inputs['action'] === 'extract-gettext')
            {
                $language = new \Nelliel\Language\Language($authorization);
                $language->extractLanguageStrings(LANGUAGE_PATH . 'extracted/extraction' . date('Y-m-d_H-i-s') . '.pot');
            }

            nel_render_main_panel($domain, $session->sessionUser());
            break;

        case 'reports':
            $reports_panel = new \Nelliel\Admin\AdminReports(nel_database(), $authorization, $domain);
            $reports_panel->actionDispatch($inputs);
            break;

        case 'board-settings':
            $board_settings_panel = new \Nelliel\Admin\AdminBoardSettings(nel_database(), $authorization, $domain);
            $board_settings_panel->actionDispatch($inputs);
            break;

        case 'bans':
            $bans_panel = new \Nelliel\Admin\AdminBans(nel_database(), $authorization, $domain);
            $bans_panel->actionDispatch($inputs);
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
                        $url = $url_constructor->dynamic(PHP_SELF,
                                ['module' => 'render', 'action' => 'view-thread',
                                    'section' => $fgsfds->getCommandData('noko', 'topic'),
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
                        $url = $url_constructor->dynamic(PHP_SELF,
                                ['module' => 'render', 'action' => 'view-index', 'section' => '0',
                                    'board_id' => $inputs['board_id']]);

                        nel_redirect($url, 2);
                    }
                    else
                    {
                        $url = $domain->reference('board_directory') . '/' . PHP_SELF2 . PHP_EXT;
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
            else if ($inputs['action'] === 'sticky' || $inputs['action'] === 'unsticky')
            {
                if ($content_id->isPost())
                {
                    $post = new \Nelliel\Content\ContentPost(nel_database(), $content_id, $domain, true);
                    $post->convertToThread();
                    $new_content_id = new \Nelliel\ContentID();
                    $new_content_id->thread_id = $content_id->post_id;
                    $new_content_id->post_id = $content_id->post_id;
                    $new_thread = new \Nelliel\Content\ContentThread(nel_database(), $new_content_id, $domain);
                    $new_thread->sticky();
                }
                else
                {
                    $thread = new \Nelliel\Content\ContentThread(nel_database(), $content_id, $domain, true);
                    $thread->sticky();
                }
            }
            else if ($inputs['action'] === 'lock' || $inputs['action'] === 'unlock')
            {
                $thread = new \Nelliel\Content\ContentThread(nel_database(), $content_id, $domain, true);
                $thread->lock();
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
            else if ($inputs['action'] === 'load-panel')
            {
                $threads_panel = new \Nelliel\Admin\AdminThreads(nel_database(), $authorization, $domain);
                $threads_panel->actionDispatch($inputs);
            }
            else
            {
                if (isset($_POST['form_submit_report']))
                {
                    $reports_panel = new \Nelliel\Admin\AdminReports(nel_database(), $authorization, $domain);
                    $reports_panel->actionDispatch($inputs);

                    if ($session->isActive() && $session->inModmode($inputs['board_id']))
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF .
                                '?module=render&action=view-index&section=0&board_id=' . $inputs['board_id'] . '">';
                    }
                    else
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . $domain->reference('board_directory') . '/' .
                                PHP_SELF2 . PHP_EXT . '">';
                    }
                }

                if (isset($_POST['form_submit_delete']))
                {
                    $thread_handler = new \Nelliel\ThreadHandler(nel_database(), $inputs['board_id']);
                    $thread_handler->processContentDeletes();

                    if ($session->isActive() && $session->inModmode($inputs['board_id']))
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF .
                                '?module=render&action=view-index&section=0&board_id=' . $inputs['board_id'] . '">';
                    }
                    else
                    {
                        echo '<meta http-equiv="refresh" content="1;URL=' . $domain->reference('board_directory') . '/' .
                                PHP_SELF2 . PHP_EXT . '">';
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

        case 'multi':
            $content_id = new \Nelliel\ContentID($inputs['content_id']);

            if ($inputs['action'] === 'ban.delete-post' || $inputs['action'] === 'ban.delete-thread')
            {
                if ($inputs['action'] === 'ban.delete-post')
                {
                    $post = new \Nelliel\Content\ContentPost(nel_database(), $content_id, $domain, true);
                    $post->remove();
                }
                else if ($inputs['action'] === 'ban.delete-thread')
                {
                    $thread = new \Nelliel\Content\ContentThread(nel_database(), $content_id, $domain, true);
                    $thread->remove();
                }

                $regen = new \Nelliel\Regen();
                $regen->threads($domain, true, $content_id->thread_id);
                $regen->index($domain);
                $inputs['action'] = 'new';
                $bans_panel = new \Nelliel\Admin\AdminBans(nel_database(), $authorization, $domain);
                $bans_panel->actionDispatch($inputs);
            }

            break;

        case 'templates':
            $templates_panel = new \Nelliel\Admin\AdminTemplates(nel_database(), $authorization, $domain);
            $templates_panel->actionDispatch($inputs);
            break;

        case 'filetypes':
            $filetypes_panel = new \Nelliel\Admin\AdminFiletypes(nel_database(), $authorization, $domain);
            $filetypes_panel->actionDispatch($inputs);
            break;

        case 'styles':
            $styles_panel = new \Nelliel\Admin\AdminStyles(nel_database(), $authorization, $domain);
            $styles_panel->actionDispatch($inputs);
            break;

        case 'permissions':
            $permissions_panel = new \Nelliel\Admin\AdminPermissions(nel_database(), $authorization, $domain);
            $permissions_panel->actionDispatch($inputs);
            break;

        default:
            break;
    }

    nel_plugins()->processHook('nel-in-after-module-dispatch', array());
}
