<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

function nel_dispatch_preparation()
{
    nel_plugins()->processHook('nel-inb4-dispatch-prep', array());
    $authorization = new \Nelliel\Auth\Authorization(nel_database());

    if (empty($_GET) && empty($_POST))
    {
        return;
    }

    if (isset($_GET['about_nelliel']))
    {
        require_once NEL_INCLUDE_PATH . 'wat/about_nelliel.php';
        nel_about_page(new \Nelliel\DomainSite(nel_database()));
    }

    if (isset($_GET['blank']) || isset($_GET['tpilb']))
    {
        require_once NEL_INCLUDE_PATH . 'wat/blank.php';
        nel_tpilb();
    }

    $inputs = array();
    $inputs['module'] = $_GET['module'] ?? '';
    $inputs['section'] = $_GET['section'] ?? '';
    $inputs['subsection'] = $_GET['subsection'] ?? '';
    $inputs['action'] = $_GET['action'] ?? '';
    $inputs['domain_id'] = $_GET['domain_id'] ?? '';
    $inputs['board_id'] = $_GET['board_id'] ?? '';
    $inputs['content_id'] = $_GET['content-id'] ?? '';
    $inputs['modmode'] = isset($_GET['modmode']) ? true : false;
    $inputs['action-confirmed'] = isset($_GET['action-confirmed']) ? true : false;

    //$session = new \Nelliel\Account\Session();

    $goback = $_GET['goback'] ?? false;

    if ($goback)
    {
        $redirect = new \Nelliel\Redirect();
        $redirect->changeURL($_SERVER['HTTP_REFERER']);
        $redirect->doRedirect(true);
    }

    if ($inputs['board_id'] === '' || $inputs['domain_id'] === '_site_')
    {
        $domain = new \Nelliel\DomainSite(nel_database());
    }
    else
    {
        $domain = new \Nelliel\DomainBoard($inputs['board_id'], nel_database());
    }

    $inputs = nel_plugins()->processHook('nel-in-after-dispatch-prep', [$domain], $inputs);

    $snacks = new \Nelliel\Snacks(nel_database(), new \Nelliel\BanHammer(nel_database()));
    $snacks->applyBan($domain, $inputs);
    $snacks->checkHoneypot($domain);

    $inputs = nel_module_dispatch($inputs, $domain);
}

function nel_module_dispatch(array $inputs, Domain $domain)
{
    $inputs = nel_plugins()->processHook('nel-inb4-module-dispatch', [$domain], $inputs);
    $authorization = new \Nelliel\Auth\Authorization($domain->database());

    switch ($inputs['module'])
    {
        case 'captcha':
            $captcha = new \Nelliel\CAPTCHA($domain);
            $captcha->dispatch($inputs);
            break;

        case 'account':
            $account_dispatch = new \Nelliel\Account\Dispatch($domain);
            $account_dispatch->dispatch($inputs);
            break;

        case 'admin':
            $admin_dispatch = new \Nelliel\Admin\Dispatch($domain, $authorization);
            $admin_dispatch->dispatch($inputs);
            break;

        case 'render':
            $inputs['index'] = $_GET['index'] ?? null;
            $inputs['thread'] = $_GET['thread'] ?? null;
            $session = new \Nelliel\Account\Session();

            if ($inputs['action'] === 'view-index')
            {
                $output_index = new \Nelliel\Output\OutputIndex($domain, false);
                $output_index->render(['thread_id' => 0], false);
            }
            else if ($inputs['action'] === 'view-thread')
            {
                $output_thread = new \Nelliel\Output\OutputThread($domain, false);
                $output_thread->render(['thread_id' => intval($inputs['thread']), 'command' => 'view-thread'], false);
            }
            else if ($inputs['action'] === 'expand-thread')
            {
                $output_thread = new \Nelliel\Output\OutputThread($domain, false);
                $output_thread->render(['thread_id' => intval($inputs['thread']), 'command' => 'expand-thread'], false);
            }
            else if ($inputs['action'] === 'collapse-thread')
            {
                $output_thread = new \Nelliel\Output\OutputThread($domain, false);
                $output_thread->render(['thread_id' => intval($inputs['thread']), 'command' => 'collapse-thread'],
                        false);
            }

            break;

        /*case 'main-panel':
            $session = new \Nelliel\Account\Session();
            $session->loggedInOrError();

            if ($domain->id() !== '_site_')
            {
                $output_board_panel = new \Nelliel\Output\OutputPanelBoard($domain, false);
                $output_board_panel->render(['user' => $session->sessionUser()], false);
            }
            else
            {
                $output_main_panel = new \Nelliel\Output\OutputPanelMain($domain, false);
                $output_main_panel->render(['user' => $session->sessionUser()], false);
            }

            break;*/

        case 'language':
            $language_dispatch = new \Nelliel\Language\Dispatch($domain, $authorization);
            $language_dispatch->dispatch($inputs);
            break;

        case 'content':

            break;

        case 'threads':
            $content_id = new \Nelliel\Content\ContentID($inputs['content_id']);
            $fgsfds = new \Nelliel\FGSFDS();
            $session = new \Nelliel\Account\Session();

            if ($inputs['action'] === 'new-post')
            {
                $new_post = new \Nelliel\Post\NewPost($domain);
                $new_post->processPost();

                $redirect = new \Nelliel\Redirect();
                $redirect->doRedirect(true);

                if ($fgsfds->getCommand('noko') !== false)
                {
                    if ($session->inModmode($domain))
                    {
                        $url_constructor = new \Nelliel\URLConstructor();
                        $url = $url_constructor->dynamic(NEL_MAIN_SCRIPT,
                                ['module' => 'render', 'action' => 'view-thread',
                                    'thread' => $fgsfds->getCommandData('noko', 'topic'),
                                    'board_id' => $inputs['board_id'], 'modmode' => 'true']);
                        $redirect->changeURL($url);
                    }
                    else
                    {
                        $url_constructor = new \Nelliel\URLConstructor();
                        $url = $domain->reference('board_directory') . '/' . $domain->reference('page_dir') . '/' .
                                $fgsfds->getCommandData('noko', 'topic') . '/thread-' .
                                $fgsfds->getCommandData('noko', 'topic') . '.html';
                        $redirect->changeURL($url);
                    }
                }
                else
                {
                    if ($session->inModmode($domain))
                    {
                        $redirect->changeURL(
                                NEL_MAIN_SCRIPT . '?module=render&action=view-index&index=0&board_id=' .
                                $inputs['board_id'] . '&modmode=true');
                    }
                    else
                    {
                        $redirect->changeURL(
                                $domain->reference('board_directory') . '/' . NEL_MAIN_INDEX . NEL_PAGE_EXT);
                    }
                }

                nel_clean_exit();
            }

            $redirect = new \Nelliel\Redirect();
            $redirect->doRedirect(true);

            if (isset($_POST['form_submit_report']))
            {
                $report = new \Nelliel\Report($domain);
                $report->submit();

                if ($session->inModmode($domain))
                {
                    $redirect->changeURL(
                            NEL_MAIN_SCRIPT . '?module=render&action=view-index&index=0&board_id=' . $inputs['board_id'] .
                            '&modmode=true');
                }
                else
                {
                    $redirect->changeURL($domain->reference('board_directory') . '/' . NEL_MAIN_INDEX . NEL_PAGE_EXT);
                }
            }

            if (isset($_POST['form_submit_delete']))
            {
                $thread_handler = new \Nelliel\ThreadHandler($domain);
                $thread_handler->processContentDeletes();

                if ($session->inModmode($domain))
                {
                    $redirect->changeURL(
                            NEL_MAIN_SCRIPT . '?module=render&action=view-index&index=0&board_id=' . $inputs['board_id'] .
                            '&modmode=true');
                }
                else
                {
                    $redirect->changeURL($domain->reference('board_directory') . '/' . NEL_MAIN_INDEX . NEL_PAGE_EXT);
                }
            }

            break;

        case 'regen':
            $regen = new \Nelliel\Regen();
            $session = new \Nelliel\Account\Session();
            $session->loggedInOrError();
            $user = $session->sessionUser();

            if ($inputs['action'] === 'board-all-pages')
            {
                if (!$user->checkPermission($domain, 'perm_regen_pages'))
                {
                    nel_derp(410, _gettext('You are not allowed to regenerate board pages.'));
                }

                $regen->allBoardPages($domain);
                $archive = new \Nelliel\ArchiveAndPrune($domain, new \Nelliel\Utility\FileHandler());
                $archive->updateThreads();
                $forward = 'board';
            }

            if ($inputs['action'] === 'board-all-caches')
            {
                if (!$user->checkPermission($domain, 'perm_regen_cache'))
                {
                    nel_derp(411, _gettext('You are not allowed to regenerate board caches.'));
                }

                $regen->boardCache($domain);
                $forward = 'board';
            }

            if ($inputs['action'] === 'site-all-caches')
            {
                if (!$user->checkPermission($domain, 'perm_regen_cache'))
                {
                    nel_derp(412, _gettext('You are not allowed to regenerate site caches.'));
                }

                $regen->siteCache($domain);
                $forward = 'site';
            }

            if ($inputs['action'] === 'overboard-all-pages')
            {
                if (!$user->checkPermission($domain, 'perm_regen_pages'))
                {
                    nel_derp(413, _gettext('You are not allowed to regenerate overboard pages.'));
                }

                $regen->overboard($domain);
                $forward = 'site';
            }

            if (empty($forward) || $forward === 'site')
            {
                $output_main_panel = new \Nelliel\Output\OutputPanelMain($domain, false);
                $output_main_panel->render(['user' => $session->sessionUser()], false);
            }
            else if ($forward === 'board')
            {
                $output_board_panel = new \Nelliel\Output\OutputPanelBoard($domain, false);
                $output_board_panel->render(['user' => $session->sessionUser()], false);
            }

            break;

        default:
            break;
    }

    $inputs = nel_plugins()->processHook('nel-in-after-module-dispatch', [$domain], $inputs);
    return $inputs;
}
