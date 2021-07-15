<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Modules\Account\Session;
use Nelliel\Domains\Domain;

function nel_dispatch_preparation()
{
    nel_plugins()->processHook('nel-inb4-dispatch-prep', array());

    if (empty($_GET) && empty($_POST))
    {
        return;
    }

    if (isset($_GET['about_nelliel']))
    {
        $about_nelliel = new Nelliel\Render\OutputAboutNelliel(nel_site_domain(), false);
        $about_nelliel->render([], false);
    }

    if (isset($_GET['blank']) || isset($_GET['tpilb']))
    {
        require_once NEL_INCLUDE_PATH . 'wat/blank.php';
        nel_tpilb();
    }

    $inputs = array();
    $inputs['raw_actions'] = $_GET['actions'] ?? '';

    if (!is_array($inputs['raw_actions']))
    {
        $inputs['actions'] = [$inputs['raw_actions']];
    }
    else
    {
        $inputs['actions'] = $inputs['raw_actions'];
    }

    $inputs['module'] = $_GET['module'] ?? '';
    $inputs['section'] = $_GET['section'] ?? '';
    $inputs['subsection'] = $_GET['subsection'] ?? '';
    $inputs['domain_id'] = $_GET['domain-id'] ?? '';
    $inputs['board_id'] = $_GET['board-id'] ?? '';
    $inputs['content_id'] = $_GET['content-id'] ?? '';
    $inputs['modmode'] = isset($_GET['modmode']) ? true : false;
    $inputs['action-confirmed'] = isset($_GET['action-confirmed']) ? true : false;
    $goback = isset($_GET['goback']) ? $_GET['goback'] === 'true' : false;

    if ($goback)
    {
        $redirect = new \Nelliel\Redirect();
        $redirect->changeURL($_SERVER['HTTP_REFERER']);
        $redirect->doRedirect(true);
    }

    // Add more options here when we implement further domain types
    if (nel_true_empty($inputs['domain_id']))
    {
        if (!nel_true_empty($inputs['board_id']) && $inputs['board_id'] !== Domain::SITE)
        {
            $domain = new \Nelliel\Domains\DomainBoard($inputs['board_id'], nel_database());
        }
        else
        {
            $domain = new \Nelliel\Domains\DomainSite(nel_database());
        }
    }
    else
    {
        $domain = new \Nelliel\Domains\DomainSite(nel_database());
    }

    $inputs = nel_plugins()->processHook('nel-in-after-dispatch-prep', [$domain], $inputs);

    if ($inputs['module'] === 'threads')
    {
        $snacks = new \Nelliel\Snacks($domain, new \Nelliel\BansAccess(nel_database()));
        $snacks->applyBan();
        //$snacks->checkHoneypot();
        $dnsbl = new \Nelliel\DNSBL(nel_database());
        $dnsbl->checkIP(nel_request_ip_address());
    }

    $inputs = nel_module_dispatch($inputs, $domain);
}

function nel_module_dispatch(array $inputs, Domain $domain)
{
    $inputs = nel_plugins()->processHook('nel-inb4-module-dispatch', [$domain], $inputs);
    $authorization = new \Nelliel\Auth\Authorization($domain->database());
    $session = new \Nelliel\Modules\Account\Session();

    switch ($inputs['module'])
    {
        case 'admin':
            $admin_dispatch = new \Nelliel\Modules\Admin\Dispatch($domain, $authorization, $session);
            $admin_dispatch->dispatch($inputs);
            break;

        case 'account':
            $account_dispatch = new \Nelliel\Modules\Account\Dispatch($domain, $session);
            $account_dispatch->dispatch($inputs);
            break;

        case 'language':
            $language_dispatch = new \Nelliel\Modules\Language\Dispatch($domain, $authorization, $session);
            $language_dispatch->dispatch($inputs);
            break;

        case 'threads':
            $threads_dispatch = new \Nelliel\Modules\Threads\Dispatch($domain, $authorization, $session);
            $threads_dispatch->dispatch($inputs);
            break;

        case 'new-post':
            $new_post_dispatch = new \Nelliel\Modules\NewPost\Dispatch($domain, $authorization, $session);
            $new_post_dispatch->dispatch($inputs);
            break;



        case 'banners':
            $banners = new \Nelliel\Banners($domain);
            $banners->dispatch($inputs);
            break;

        case 'captcha':
            $captcha = new \Nelliel\CAPTCHA($domain);
            $captcha->dispatch($inputs);
            break;



        case 'render':
            $inputs['index'] = $_GET['index'] ?? null;
            $inputs['thread'] = $_GET['thread'] ?? null;

            switch ($inputs['actions'][0])
            {
                case 'view-index':
                    $output_index = new \Nelliel\Render\OutputIndex($domain, false);
                    $output_index->render(['thread_id' => 0], false);
                    break;

                case 'view-thread':
                    $output_thread = new \Nelliel\Render\OutputThread($domain, false);
                    $output_thread->render(['thread_id' => intval($inputs['thread']), 'command' => 'view-thread'],
                            false);
                    break;

                case 'expand-thread':
                    $output_thread = new \Nelliel\Render\OutputThread($domain, false);
                    $output_thread->render(['thread_id' => intval($inputs['thread']), 'command' => 'expand-thread'],
                            false);
                    break;

                case 'collapse-thread':
                    $output_thread = new \Nelliel\Render\OutputThread($domain, false);
                    $output_thread->render(['thread_id' => intval($inputs['thread']), 'command' => 'collapse-thread'],
                            false);
                    break;
            }

            break;

        case 'regen':
            $regen = new \Nelliel\Regen();
            $session->init(true);
            $session->loggedInOrError();
            $user = $session->user();
            $forward = 'site';
            $board_id = $_GET['board-id'] ?? '';

            foreach ($inputs['actions'] as $action)
            {
                switch ($action)
                {
                    case 'board-all-pages':
                        if (!$user->checkPermission($domain, 'perm_regen_pages'))
                        {
                            nel_derp(550, _gettext('You are not allowed to regenerate board pages.'));
                        }

                        $regen->allBoardPages($domain);
                        $archive = new \Nelliel\ArchiveAndPrune($domain, nel_utilities()->fileHandler());
                        $archive->updateThreads();
                        $forward = 'board';
                        break;

                    case 'board-all-caches':
                        if (!$user->checkPermission($domain, 'perm_regen_cache'))
                        {
                            nel_derp(551, _gettext('You are not allowed to regenerate board caches.'));
                        }

                        $domain->regenCache();
                        $forward = 'board';
                        break;

                    case 'site-all-caches':
                        if (!$user->checkPermission($domain, 'perm_regen_cache'))
                        {
                            nel_derp(552, _gettext('You are not allowed to regenerate site caches.'));
                        }

                        $domain->regenCache();
                        $forward = 'site';
                        break;

                    case 'overboard-all-pages':
                        if (!$user->checkPermission($domain, 'perm_regen_pages'))
                        {
                            nel_derp(553, _gettext('You are not allowed to regenerate overboard pages.'));
                        }

                        $regen->overboard($domain);
                        $forward = 'site';
                        break;
                }
            }

            if ($forward === 'site')
            {
                $output_main_panel = new \Nelliel\Render\OutputPanelMain($domain, false);
                $output_main_panel->render(['user' => $user], false);
            }
            else if ($forward === 'board')
            {
                $output_board_panel = new \Nelliel\Render\OutputPanelBoard($domain, false);
                $output_board_panel->render(['user' => $user, 'board_id' => $board_id], false);
            }

            break;

        default:
            break;
    }

    $inputs = nel_plugins()->processHook('nel-in-after-module-dispatch', [$domain], $inputs);
    return $inputs;
}
