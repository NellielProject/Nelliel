<?php

namespace Nelliel\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Redirect;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class Dispatch
{
    private $domain;
    private $authorization;
    private $session;

    function __construct(Domain $domain, Authorization $authorization, Session $session)
    {
        $this->domain = $domain;
        $this->authorization = $authorization;
        $this->session = $session;
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        $admin_handler = null;

        if (empty($inputs['actions']))
        {
            $admin_handler = $this->sections($inputs, '');
        }
        else
        {
            foreach ($inputs['actions'] as $action)
            {
                $admin_handler = $this->sections($inputs, $action);
            }
        }

        $redirect = new Redirect();
        $redirect->changeDelay(0);

        if (is_null($admin_handler))
        {
            return;
        }

        if ($admin_handler->outputMain())
        {
            $admin_handler->renderPanel();
        }
    }

    private function sections(array $inputs, string $action)
    {
        $admin_handler = null;
        $board_id = $inputs['board_id'];

        switch ($inputs['section'])
        {
            case 'bans':
                $admin_handler = new AdminBans($this->authorization, $this->domain, $this->session, $inputs);
                $this->standard($admin_handler, $action);
                break;

            case 'board-settings':
                $admin_handler = new AdminBoardSettings($this->authorization, $this->domain, $this->session, $inputs);
                $this->standard($admin_handler, $action);
                break;

            case 'board-defaults':
                $admin_handler = new AdminBoardSettings($this->authorization, $this->domain, $this->session, $inputs);
                $this->standard($admin_handler, $action);
                break;

            case 'file-filters':
                $admin_handler = new AdminFileFilters($this->authorization, $this->domain, $this->session, $inputs);
                $this->standard($admin_handler, $action);
                break;

            case 'filetypes':
                $admin_handler = new AdminFiletypes($this->authorization, $this->domain, $this->session, $inputs);

                if ($action === 'enable')
                {
                    $admin_handler->enable();
                }
                else if ($action === 'disable')
                {
                    $admin_handler->disable();
                }
                else
                {
                    $this->standard($admin_handler, $action);
                }

                break;

            case 'icon-sets':
                $admin_handler = new AdminIconSets($this->authorization, $this->domain, $this->session, $inputs);

                if ($action === 'make-default')
                {
                    $admin_handler->makeDefault();
                }
                else
                {
                    $this->standard($admin_handler, $action);
                }

                break;

            case 'ifthens':
                $admin_handler = new AdminIfThens($this->authorization, $this->domain, $this->session, $inputs);

                if ($action === 'enable')
                {
                    $admin_handler->enable();
                }
                else if ($action === 'disable')
                {
                    $admin_handler->disable();
                }
                else
                {
                    $this->standard($admin_handler, $action);
                }

                break;

            case 'logs':
                $admin_handler = new AdminLogs($this->authorization, $this->domain, $this->session, $inputs);
                $this->standard($admin_handler, $action);
                break;

            case 'manage-boards':
                $admin_handler = new AdminBoards($this->authorization, $this->domain, $this->session, $inputs);

                if ($action === 'remove')
                {
                    if ($inputs['action-confirmed'])
                    {
                        $admin_handler->remove();
                    }
                    else
                    {
                        $admin_handler->createInterstitial('remove_warning');
                    }
                }
                else if ($action === 'lock')
                {
                    $admin_handler->lock();
                }
                else if ($action === 'unlock')
                {
                    $admin_handler->unlock();
                }
                else
                {
                    $this->standard($admin_handler, $action);
                }

                break;

            case 'news':
                $admin_handler = new AdminNews($this->authorization, $this->domain, $this->session, $inputs);
                $this->standard($admin_handler, $action);
                break;

            case 'permissions':
                $admin_handler = new AdminPermissions($this->authorization, $this->domain, $this->session, $inputs);
                $this->standard($admin_handler, $action);
                break;

            case 'reports':
                $admin_handler = new AdminReports($this->authorization, $this->domain, $this->session, $inputs);
                $this->standard($admin_handler, $action);
                break;

            case 'roles':
                $admin_handler = new AdminRoles($this->authorization, $this->domain, $this->session, $inputs);
                $this->standard($admin_handler, $action);
                break;

            case 'site-settings':
                $admin_handler = new AdminSiteSettings($this->authorization, $this->domain, $this->session, $inputs);
                $this->standard($admin_handler, $action);
                break;

            case 'styles':
                $admin_handler = new AdminStyles($this->authorization, $this->domain, $this->session, $inputs);

                if ($action === 'make-default')
                {
                    $admin_handler->makeDefault();
                }
                else
                {
                    $this->standard($admin_handler, $action);
                }

                break;

            case 'templates':
                $admin_handler = new AdminTemplates($this->authorization, $this->domain, $this->session, $inputs);

                if ($action === 'make-default')
                {
                    $admin_handler->makeDefault();
                }
                else
                {
                    $this->standard($admin_handler, $action);
                }

                break;

            case 'threads':
                $admin_handler = new AdminThreads($this->authorization, $this->domain, $this->session, $inputs);

                // TODO: Refine this whenever we get threads panel updated
                if ($inputs['subsection'] === 'panel')
                {
                    $admin_handler->outputMain(true);
                }
                else
                {
                    $admin_handler->outputMain(false);
                }

                if ($action === 'sticky')
                {
                    $admin_handler->sticky();
                }
                else if ($action === 'lock')
                {
                    $admin_handler->lock();
                }
                else if ($action === 'delete')
                {
                    $admin_handler->remove();
                }
                else if ($action === 'ban')
                {
                    $admin_handler = new \Nelliel\Admin\AdminBans($this->authorization, $this->domain, $this->session,
                            $inputs);
                    $admin_handler->creator();
                }
                else if ($action === 'bandelete')
                {
                    $admin_handler->banDelete();
                }
                else if ($action === 'sage')
                {
                    $admin_handler->permasage();
                }
                else if ($action === 'expand')
                {
                    ; // TODO: Figure this out better
                }
                else
                {
                    $this->standard($admin_handler, $action);
                }

                break;

            case 'users':
                $admin_handler = new AdminUsers($this->authorization, $this->domain, $this->session, $inputs);
                $this->standard($admin_handler, $action);
                break;

            case 'site-main-panel':
                $output_main_panel = new \Nelliel\Render\OutputPanelMain($this->domain, false);
                $output_main_panel->render([], false);
                break;

            case 'board-main-panel':
                $output_board_panel = new \Nelliel\Render\OutputPanelBoard($this->domain, false);
                $output_board_panel->render(['board_id' => $board_id], false);
                break;
        }

        return $admin_handler;
    }

    private function standard(Admin $admin_handler, string $action)
    {
        switch ($action)
        {
            case 'new':
                $admin_handler->creator();
                break;

            case 'add':
                $admin_handler->add();
                break;

            case 'edit':
                $admin_handler->editor();
                break;

            case 'update':
                $admin_handler->update();
                break;

            case 'remove':
                $admin_handler->remove();
                break;
        }
    }
}