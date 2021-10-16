<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\ArchiveAndPrune;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelBoard;
use Nelliel\Output\OutputPanelMain;

class DispatchModules extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs)
    {
        switch ($inputs['module'])
        {
            /*case 'account':
                $account_dispatch = new DispatchAccount($this->authorization, $this->domain, $this->session);
                $account_dispatch->dispatch($inputs);
                break;*/

            case 'admin':
                $admin_dispatch = new DispatchAdmin($this->authorization, $this->domain, $this->session);
                $admin_dispatch->dispatch($inputs);
                break;

            /*case 'banners':
                $banners_dispatch = new DispatchBanners($this->authorization, $this->domain, $this->session);
                $banners_dispatch->dispatch($inputs);
                break;*/

            /*case 'anti-spam':
                $anti_spam_dispatch = new DispatchAntiSpam($this->authorization, $this->domain, $this->session);
                $anti_spam_dispatch->dispatch($inputs);
                break;*/

            /*case 'language':
                $language_dispatch = new DispatchLanguage($this->authorization, $this->domain, $this->session);
                $language_dispatch->dispatch($inputs);
                break;*/

            case 'threads':
                $threads_dispatch = new DispatchThreads($this->authorization, $this->domain, $this->session);
                $threads_dispatch->dispatch($inputs);
                break;

            case 'new-post':
                $new_post_dispatch = new DispatchNewPost($this->authorization, $this->domain, $this->session);
                $new_post_dispatch->dispatch($inputs);
                break;

            case 'output':
                $output_dispatch = new DispatchOutput($this->authorization, $this->domain, $this->session);
                $output_dispatch->dispatch($inputs);
                break;

            case 'regen':
                $regen = new \Nelliel\Regen();
                $this->session->init(true);
                $this->session->loggedInOrError();
                $user = $this->session->user();
                $forward = 'site';
                $board_id = $_GET['board-id'] ?? '';

                foreach ($inputs['actions'] as $action)
                {
                    switch ($action)
                    {
                        case 'board-all-pages':
                            if (!$user->checkPermission($this->domain, 'perm_regen_pages'))
                            {
                                nel_derp(500, _gettext('You are not allowed to regenerate board pages.'));
                            }

                            $regen->allBoardPages($this->domain);
                            $archive = new ArchiveAndPrune($this->domain, nel_utilities()->fileHandler());
                            $archive->updateThreads();
                            $forward = 'board';
                            break;

                        case 'board-all-caches':
                            if (!$user->checkPermission($this->domain, 'perm_regen_cache'))
                            {
                                nel_derp(501, _gettext('You are not allowed to regenerate board caches.'));
                            }

                            $this->domain->regenCache();
                            $forward = 'board';
                            break;

                        case 'site-all-caches':
                            if (!$user->checkPermission($this->domain, 'perm_regen_cache'))
                            {
                                nel_derp(502, _gettext('You are not allowed to regenerate site caches.'));
                            }

                            $this->domain->regenCache();
                            $forward = 'site';
                            break;

                        case 'site-all-pages':
                            if (!$user->checkPermission($this->domain, 'perm_regen_pages'))
                            {
                                nel_derp(503, _gettext('You are not allowed to regenerate site pages.'));
                            }

                            $regen->allSitePages($this->domain);
                            $forward = 'site';
                            break;

                        case 'overboard-all-pages':
                            if (!$user->checkPermission($this->domain, 'perm_regen_overboard'))
                            {
                                nel_derp(504, _gettext('You are not allowed to regenerate overboard pages.'));
                            }

                            $regen->overboard($this->domain);
                            $forward = 'site';
                            break;
                    }
                }

                if ($forward === 'site')
                {
                    $output_main_panel = new OutputPanelMain($this->domain, false);
                    $output_main_panel->render(['user' => $user], false);
                }
                else if ($forward === 'board')
                {
                    $output_board_panel = new OutputPanelBoard($this->domain, false);
                    $output_board_panel->render(['user' => $user, 'board_id' => $board_id], false);
                }

                break;

            default:
                nel_derp(250, _gettext('The selected module is invalid.'));
                break;
        }
    }
}