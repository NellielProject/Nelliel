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

class DispatchRegen extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        $regen = new \Nelliel\Regen();
        $user = $this->session->user();
        $forward = 'site';

        switch ($inputs['section']) {
            case 'pages':
                if ($this->domain->id() === Domain::SITE) {
                    if (!$user->checkPermission($this->domain, 'perm_regen_pages')) {
                        nel_derp(503, _gettext('You are not allowed to regenerate site pages.'));
                    }

                    $regen->allSitePages($this->domain);
                    $forward = 'site';
                } else {
                    if (!$user->checkPermission($this->domain, 'perm_regen_pages')) {
                        nel_derp(500, _gettext('You are not allowed to regenerate board pages.'));
                    }

                    $regen->allBoardPages($this->domain);
                    $archive = new ArchiveAndPrune($this->domain, nel_utilities()->fileHandler());
                    $archive->updateThreads();
                    $forward = 'board';
                }

                break;

            case 'cache':
                if ($this->domain->id() === Domain::SITE) {
                    if (!$user->checkPermission($this->domain, 'perm_regen_cache')) {
                        nel_derp(502, _gettext('You are not allowed to regenerate site caches.'));
                    }

                    $this->domain->regenCache();
                    $forward = 'site';
                } else {
                    if (!$user->checkPermission($this->domain, 'perm_regen_cache')) {
                        nel_derp(501, _gettext('You are not allowed to regenerate board caches.'));
                    }

                    $this->domain->regenCache();
                    $forward = 'board';
                }

                break;

            case 'overboard':
                if (!$user->checkPermission($this->domain, 'perm_regen_overboard')) {
                    nel_derp(504, _gettext('You are not allowed to regenerate overboard pages.'));
                }

                $regen->overboard($this->domain);
                $forward = 'site';

                break;
        }

        if ($forward === 'site') {
            $output_main_panel = new OutputPanelMain($this->domain, false);
            $output_main_panel->render(['user' => $user], false);
        } else if ($forward === 'board') {
            $output_board_panel = new OutputPanelBoard($this->domain, false);
            $output_board_panel->render(['user' => $user, 'board_id' => $this->domain->id()], false);
        }
    }
}