<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Functions;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Account\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelMain;

class DispatchRegen extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $regen = new Regen();
        $user = $this->session->user();
        $forward = 'site';

        switch ($inputs['section']) {
            case 'pages':
                if ($this->domain->id() === Domain::SITE) {
                    if (!$user->checkPermission($this->domain, 'perm_regen_pages')) {
                        nel_derp(500, __('You are not allowed to regenerate site pages.'), 403);
                    }

                    $regen->sitePages($this->domain);
                    $forward = 'site';
                } else if ($this->domain->id() === Domain::GLOBAL) {
                    if (!$user->checkPermission($this->domain, 'perm_regen_pages')) {
                        nel_derp(502, __('You are not allowed to globally regenerate board pages.'), 403);
                    }

                    $regen->allBoards(true, false);
                    $forward = 'global';
                } else {
                    if (!$user->checkPermission($this->domain, 'perm_regen_pages')) {
                        nel_derp(504, __('You are not allowed to regenerate pages on this board.'), 403);
                    }

                    $regen->boardPages($this->domain);
                    $forward = 'board';
                }

                break;

            case 'cache':
                if ($this->domain->id() === Domain::SITE) {
                    if (!$user->checkPermission($this->domain, 'perm_regen_cache')) {
                        nel_derp(501, __('You are not allowed to regenerate site caches.'), 403);
                    }

                    $this->domain->regenCache();
                    $forward = 'site';
                } else if ($this->domain->id() === Domain::GLOBAL) {
                    if (!$user->checkPermission($this->domain, 'perm_regen_cache')) {
                        nel_derp(503, __('You are not allowed to globally regenerate board caches.'), 403);
                    }

                    $this->domain->regenCache();
                    $forward = 'global';
                } else {
                    if (!$user->checkPermission($this->domain, 'perm_regen_cache')) {
                        nel_derp(505, __('You are not allowed to regenerate caches on this board.'), 403);
                    }

                    $this->domain->regenCache();
                    $forward = 'board';
                }

                break;

            case 'overboard':
                if (!$user->checkPermission($this->domain, 'perm_regen_overboard')) {
                    nel_derp(506, __('You are not allowed to regenerate overboard pages.'), 403);
                }

                $regen->overboard($this->domain);
                $forward = 'site';

                break;
        }

        if ($forward === 'site') {
            $output_main_panel = new OutputPanelMain($this->domain, false);
            $output_main_panel->site(['user' => $user], false);
        } else if ($forward === 'global') {
            $output_board_panel = new OutputPanelMain($this->domain, false);
            $output_board_panel->global(['user' => $user, 'board_id' => $this->domain->id()], false);
        } else if ($forward === 'board') {
            $output_board_panel = new OutputPanelMain($this->domain, false);
            $output_board_panel->board(['user' => $user, 'board_id' => $this->domain->id()], false);
        }
    }
}