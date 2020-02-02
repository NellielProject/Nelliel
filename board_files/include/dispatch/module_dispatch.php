<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

function nel_module_dispatch(array $inputs, Domain $domain)
{
    $authorization = new \Nelliel\Auth\Authorization($domain->database());
    $inputs = nel_plugins()->processHook('nel-inb4-module-dispatch', [$domain], $inputs);

    switch ($inputs['module'])
    {
        case 'captcha':
            $captcha = new \Nelliel\CAPTCHA($domain);

            switch ($inputs['action'])
            {
                case 'generate':
                    $captcha->generate();
                    break;
            }

        case 'account':
            switch ($inputs['action'])
            {
                case 'login':
                    $session = new \Nelliel\Account\Session();

                    if (empty($_POST))
                    {
                        $output_login = new \Nelliel\Output\OutputLoginPage($domain);
                        $output_login->render(['dotdot' => ''], false);
                    }
                    else
                    {
                        $session->login();
                        $output_main_panel = new \Nelliel\Output\OutputPanelAccount($domain);
                        $output_main_panel->render(['user' => $session->sessionUser()], false);
                    }

                    break;

                case 'logout':
                    $session = new \Nelliel\Account\Session();
                    $session->logout();
                    break;

                case 'register':
                    if (empty($_POST))
                    {
                        $output_login = new \Nelliel\Output\OutputRegisterPage($domain);
                        $output_login->render(['dotdot' => ''], false);
                    }
                    else
                    {
                        $register = new \Nelliel\Account\Register($authorization, $domain->database());
                        $register->new();
                    }

                    break;
            }

            break;

        case 'render':
            $inputs['index'] = $_GET['index'] ?? null;
            $inputs['thread'] = $_GET['thread'] ?? null;
            $session = new \Nelliel\Account\Session();

            switch ($inputs['action'])
            {
                case 'view-index':
                    $output_index = new \Nelliel\Output\OutputIndex($domain);
                    $output_index->render(['write' => false, 'thread_id' => 0], false);
                    break;

                case 'view-thread':
                    $output_thread = new \Nelliel\Output\OutputThread($domain);
                    $output_thread->render(
                            ['write' => false, 'thread_id' => intval($inputs['thread']), 'command' => $inputs['action']],
                            false);
                    break;

                case 'expand-thread':
                    $output_thread = new \Nelliel\Output\OutputThread($domain);
                    $output_thread->render(
                            ['write' => false, 'thread_id' => intval($inputs['thread']), 'command' => $inputs['action']],
                            false);
                    break;

                case 'collapse-thread':
                    $output_thread = new \Nelliel\Output\OutputThread($domain);
                    $output_thread->render(
                            ['write' => false, 'thread_id' => intval($inputs['thread']), 'command' => $inputs['action']],
                            false);
                    break;
            }

            break;

        case 'main-panel':
            $session = new \Nelliel\Account\Session();

            if ($domain->id() !== '_site_')
            {
                $output_board_panel = new \Nelliel\Output\OutputPanelBoard($domain);
                $output_board_panel->render(['user' => $session->sessionUser()], false);
            }
            else
            {
                $output_main_panel = new \Nelliel\Output\OutputPanelMain($domain);
                $output_main_panel->render(['user' => $session->sessionUser()], false);
            }

            break;

        case 'users':
            $users_admin = new \Nelliel\Admin\AdminUsers($authorization, $domain);
            $users_admin->actionDispatch($inputs);
            break;

        case 'roles':
            $roles_admin = new \Nelliel\Admin\AdminRoles($authorization, $domain);
            $roles_admin->actionDispatch($inputs);
            break;

        case 'site-settings':
            $site_settings_admin = new \Nelliel\Admin\AdminSiteSettings($authorization, $domain);
            $site_settings_admin->actionDispatch($inputs);
            break;

        case 'manage-boards':
            $create_board_admin = new \Nelliel\Admin\AdminBoards($authorization, $domain);
            $create_board_admin->actionDispatch($inputs);
            break;

        case 'file-filters':
            $file_filters_admin = new \Nelliel\Admin\AdminFileFilters($authorization, $domain);
            $file_filters_admin->actionDispatch($inputs);
            break;

        case 'board-defaults':
            $board_settings_admin = new \Nelliel\Admin\AdminBoardSettings($authorization, $domain);
            $board_settings_admin->actionDispatch($inputs);
            break;

        case 'language':
            $session = new \Nelliel\Account\Session();

            if ($inputs['action'] === 'extract-gettext')
            {
                $language = new \Nelliel\Language\Language();
                $language->extractLanguageStrings($domain, $session->sessionUser(), 'nelliel', LC_MESSAGES);
            }

            $output_main_panel = new \Nelliel\Output\OutputPanelMain($domain);
            $output_main_panel->render(['user' => $session->sessionUser()], false);
            break;

        case 'reports':
            $reports_admin = new \Nelliel\Admin\AdminReports($authorization, $domain);
            $reports_admin->actionDispatch($inputs);
            break;

        case 'board-settings':
            $board_settings_admin = new \Nelliel\Admin\AdminBoardSettings($authorization, $domain);
            $board_settings_admin->actionDispatch($inputs);
            break;

        case 'bans':
            $bans_admin = new \Nelliel\Admin\AdminBans($authorization, $domain);
            $bans_admin->actionDispatch($inputs);
            break;

        case 'threads-admin':
            $threads_admin = new \Nelliel\Admin\AdminThreads($authorization, $domain);
            $threads_admin->actionDispatch($inputs);

            if ($inputs['action'] === 'ban-delete')
            {
                $bans_admin = new \Nelliel\Admin\AdminBans($authorization, $domain);
                $bans_admin->actionDispatch($inputs);
            }

            break;

        case 'threads':
            $content_id = new \Nelliel\ContentID($inputs['content_id']);
            $fgsfds = new \Nelliel\FGSFDS();
            $session = new \Nelliel\Account\Session();

            if ($inputs['action'] === 'new-post')
            {
                $new_post = new \Nelliel\Post\NewPost($domain);
                $new_post->processPost();

                if ($fgsfds->getCommand('noko') !== false)
                {
                    if ($session->inModmode($domain))
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
                    if ($session->inModmode($domain))
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
                $post = new \Nelliel\Content\ContentPost($content_id, $domain, true);
                $post->remove();
            }
            else if ($inputs['action'] === 'delete-thread')
            {
                $thread = new \Nelliel\Content\ContentThread($content_id, $domain, true);
                $thread->remove();
            }
            else if ($inputs['action'] === 'delete-file')
            {
                $file = new \Nelliel\Content\ContentFile($content_id, $domain, true);
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
                    $reports_admin = new \Nelliel\Admin\AdminReports($authorization, $domain);
                    $reports_admin->actionDispatch($inputs);

                    if ($session->inModmode($domain))
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
                    $thread_handler = new \Nelliel\ThreadHandler($domain->database(), $domain);
                    $thread_handler->processContentDeletes();

                    if ($session->inModmode($domain))
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
            $session = new \Nelliel\Account\Session();
            $user = $session->sessionUser();

            if ($inputs['action'] === 'board-all-pages')
            {
                if (!$user->checkPermission($domain, 'perm_regen_pages'))
                {
                    nel_derp(410, _gettext('You are not allowed to regenerate board pages.'));
                }

                $regen->allBoardPages($domain);
                $archive = new \Nelliel\ArchiveAndPrune($domain->database(), $domain, new \Nelliel\FileHandler());
                $archive->updateThreads();
            }
            else if ($inputs['action'] === 'board-all-caches')
            {
                if (!$user->checkPermission($domain, 'perm_regen_cache'))
                {
                    nel_derp(411, _gettext('You are not allowed to regenerate board caches.'));
                }

                $regen->boardCache($domain);
            }
            else if ($inputs['action'] === 'site-all-caches')
            {
                if (!$user->checkPermission($domain, 'perm_regen_caches'))
                {
                    nel_derp(412, _gettext('You are not allowed to regenerate site caches.'));
                }

                $regen->siteCache($domain);
            }

            $output_board_panel = new \Nelliel\Output\OutputPanelBoard($domain);
            $output_board_panel->render(['user' => $session->sessionUser()], false);
            break;

        case 'templates':
            $templates_admin = new \Nelliel\Admin\AdminTemplates($authorization, $domain);
            $templates_admin->actionDispatch($inputs);
            break;

        case 'filetypes':
            $filetypes_admin = new \Nelliel\Admin\AdminFiletypes($authorization, $domain);
            $filetypes_admin->actionDispatch($inputs);
            break;

        case 'styles':
            $styles_admin = new \Nelliel\Admin\AdminStyles($authorization, $domain);
            $styles_admin->actionDispatch($inputs);
            break;

        case 'icon-sets':
            $icon_sets_admin = new \Nelliel\Admin\AdminIconSets($authorization, $domain);
            $icon_sets_admin->actionDispatch($inputs);
            break;

        case 'news':
            $news_admin = new \Nelliel\Admin\AdminNews($authorization, $domain);
            $news_admin->actionDispatch($inputs);
            break;

        case 'logs':
            $logs_admin = new \Nelliel\Admin\AdminLogs($authorization, $domain);
            $logs_admin->actionDispatch($inputs);
            break;

        default:
            break;
    }

    $inputs = nel_plugins()->processHook('nel-in-after-module-dispatch', [$domain], $inputs);
    return $inputs;
}
