<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Functions;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Redirect;
use Nelliel\Account\Session;
use Nelliel\Admin\AdminBans;
use Nelliel\Admin\AdminThreads;
use Nelliel\Auth\Authorization;
use Nelliel\Content\ContentID;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Content\Thread;
use Nelliel\Output\OutputPanelThreads;

class DispatchModeration extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs): void
    {
        $this->session->init(true);
        $this->session->loggedInOrError();
        $this->session->toggleModMode();

        switch ($inputs['section']) {
            case 'modmode':
                $redirect = new Redirect();
                $redirect->URL($_SERVER['HTTP_REFERER'] ?? '');
                $redirect->delay(0);
                $admin_threads = new AdminThreads($this->authorization, $this->domain, $this->session);
                $admin_bans = new AdminBans($this->authorization, $this->domain, $this->session);
                $content_id = new ContentID($inputs['content_id']);

                if (!isset($inputs['action'])) {

                    break;
                }

                switch ($inputs['action']) {
                    case 'ban':
                        $admin_bans->creator($content_id);
                        break;

                    case 'ban-delete':
                        $admin_bans->creator($content_id);
                        $admin_threads->delete($content_id);
                        break;

                    case 'delete':
                        $admin_threads->delete($content_id);
                        $redirect->doRedirect(true);
                        break;

                    case 'delete-by-ip':
                        $admin_threads->deleteByIP($content_id);
                        $redirect->doRedirect(true);
                        break;

                    case 'global-delete-by-ip':
                        $admin_threads->globalDeleteByIP($content_id);
                        $redirect->doRedirect(true);
                        break;

                    case 'lock':
                    case 'unlock':
                        $admin_threads->lock($content_id);
                        $redirect->doRedirect(true);
                        break;

                    case 'sticky':
                    case 'unsticky':
                        $admin_threads->sticky($content_id);
                        $redirect->doRedirect(true);
                        break;

                    case 'sage':
                    case 'unsage':
                        $admin_threads->sage($content_id);
                        $redirect->doRedirect(true);
                        break;

                    case 'cyclic':
                    case 'non-cyclic':
                        $admin_threads->cyclic($content_id);
                        $redirect->doRedirect(true);
                        break;

                    case 'edit':
                        if ($inputs['method'] === 'GET') {
                            $admin_threads->editor($content_id);
                        }

                        if ($inputs['method'] === 'POST') {
                            $admin_threads->update($content_id);
                            $redirect->doRedirect(true);
                        }

                        break;

                    case 'move':
                        if ($inputs['method'] === 'GET') {
                            $output_panel_threads = new OutputPanelThreads($this->domain, false);
                            $output_panel_threads->move(['content_id' => $content_id], false);
                        }

                        if ($inputs['method'] === 'POST') {
                            $admin_threads->move($content_id);
                            $redirect->doRedirect(true);
                        }
                        // TODO: Make sure redirect works
                        // Also check/update permissions if needed

                        break;
                }

            default:
                break;
        }
    }
}