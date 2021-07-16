<?php
declare(strict_types = 1);

namespace Nelliel\Modules\Admin;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Redirect;
use Nelliel\Modules\DispatchErrors;
use Nelliel\Modules\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;

class Dispatch
{
    private $domain;
    private $authorization;
    private $session;

    use DispatchErrors;

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
        $redirect = new Redirect();
        $redirect->changeDelay(0);
        $admin_handler = null;
        $board_id = $inputs['board_id'];

        if (empty($inputs['actions']))
        {
            $this->invalidAction();
        }

        switch ($inputs['section'])
        {
            case 'bans':
                $admin_handler = new AdminBans($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'board-settings':
                $admin_handler = new AdminBoardSettings($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'board-defaults':
                $admin_handler = new AdminBoardSettings($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'file-filters':
                $admin_handler = new AdminFileFilters($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'filetypes':
                $admin_handler = new AdminFiletypes($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'icon-sets':
                $admin_handler = new AdminIconSets($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'logs':
                $admin_handler = new AdminLogs($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'manage-boards':
                $admin_handler = new AdminBoards($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'news':
                $admin_handler = new AdminNews($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'permissions':
                $admin_handler = new AdminPermissions($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'reports':
                $admin_handler = new AdminReports($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'roles':
                $admin_handler = new AdminRoles($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'site-settings':
                $admin_handler = new AdminSiteSettings($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'styles':
                $admin_handler = new AdminStyles($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'templates':
                $admin_handler = new AdminTemplates($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'threads':
                $admin_handler = new AdminThreads($this->authorization, $this->domain, $this->session);

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
                else if ($action === 'delete-by-ip')
                {
                    $admin_handler->removeByIP();
                }
                else if ($action === 'ban')
                {
                    $admin_handler = new \Nelliel\Modules\Admin\AdminBans($this->authorization, $this->domain,
                            $this->session, $inputs);
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
                else if ($action === 'cyclic')
                {
                    $admin_handler->cyclic();
                }
                else if ($action === 'expand')
                {
                    ; // TODO: Figure this out better
                }
                else
                {
                    $admin_handler->dispatch($inputs);
                }

                break;

            case 'users':
                $admin_handler = new AdminUsers($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'site-main-panel':
                $output_main_panel = new \Nelliel\Render\OutputPanelMain($this->domain, false);
                $output_main_panel->render([], false);
                break;

            case 'board-main-panel':
                $output_board_panel = new \Nelliel\Render\OutputPanelBoard($this->domain, false);
                $output_board_panel->render(['board_id' => $board_id], false);
                break;

            case 'staff-board':
                $admin_handler = new AdminStaffBoard($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'dnsbl':
                $admin_handler = new AdminDNSBL($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'wordfilters':
                $admin_handler = new AdminWordfilters($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            default:
                $this->invalidSection();
        }

        if (is_null($admin_handler))
        {
            return;
        }

        if ($admin_handler->outputMain())
        {
            $admin_handler->panel();
        }
    }
}