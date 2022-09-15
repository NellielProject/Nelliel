<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Redirect;
use Nelliel\Account\Session;
use Nelliel\Admin\AdminBans;
use Nelliel\Admin\AdminBoardDefaults;
use Nelliel\Admin\AdminBoards;
use Nelliel\Admin\AdminCapcodes;
use Nelliel\Admin\AdminContentOps;
use Nelliel\Admin\AdminEmbeds;
use Nelliel\Admin\AdminFileFilters;
use Nelliel\Admin\AdminFiletypeCategories;
use Nelliel\Admin\AdminFiletypes;
use Nelliel\Admin\AdminImageSets;
use Nelliel\Admin\AdminLogs;
use Nelliel\Admin\AdminNews;
use Nelliel\Admin\AdminNoticeboard;
use Nelliel\Admin\AdminPages;
use Nelliel\Admin\AdminPermissions;
use Nelliel\Admin\AdminPlugins;
use Nelliel\Admin\AdminReports;
use Nelliel\Admin\AdminRoles;
use Nelliel\Admin\AdminStyles;
use Nelliel\Admin\AdminTemplates;
use Nelliel\Admin\AdminThreads;
use Nelliel\Admin\AdminUsers;
use Nelliel\Admin\AdminWordFilters;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputBoardlist;
use Nelliel\Output\OutputPanelBoard;
use Nelliel\Output\OutputPanelMain;

class DispatchAdmin extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs)
    {
        $redirect = new Redirect();
        $redirect->delay(0);
        $admin_handler = null;
        $board_id = $inputs['board_id'];

        if (empty($inputs['actions'])) {
            $this->invalidAction();
        }

        switch ($inputs['section']) {
            // TODO: Remove this once we figure out mod links
            case 'bans':
                $admin_handler = new AdminBans($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            /*case 'board-settings':
                $admin_handler = new AdminBoardSettings($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'board-defaults':
                $admin_handler = new AdminBoardDefaults($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'file-filters':
                $admin_handler = new AdminFileFilters($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'filetypes':
                $admin_handler = new AdminFiletypes($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'image-sets':
                $admin_handler = new AdminImageSets($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'logs':
                $admin_handler = new AdminLogs($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'manage-boards':
                $admin_handler = new AdminBoards($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'news':
                $admin_handler = new AdminNews($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'permissions':
                $admin_handler = new AdminPermissions($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            case 'reports':
                $admin_handler = new AdminReports($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'roles':
                $admin_handler = new AdminRoles($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            /*case 'site-settings':
                $admin_handler = new AdminSiteSettings($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'styles':
                $admin_handler = new AdminStyles($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'templates':
                $admin_handler = new AdminTemplates($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            case 'threads':
                $admin_handler = new AdminThreads($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            case 'users':
                $admin_handler = new AdminUsers($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;

            /*case 'site-main-panel':
                $output_main_panel = new OutputPanelMain($this->domain, false);
                $output_main_panel->render([], false);
                break;*/

            /*case 'board-main-panel':
                $output_board_panel = new OutputPanelBoard($this->domain, false);
                $output_board_panel->render(['board_id' => $board_id], false);
                break;*/

            /*case 'word-filters':
                $admin_handler = new AdminWordFilters($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'blotter':
                $admin_handler = new AdminBlotter($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'pages':
                $admin_handler = new AdminPages($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'boardlist':
                $output_boardlist = new OutputBoardlist($this->domain, false);
                $output_boardlist->render([], false);
                break;*/

            /*case 'embeds':
                $admin_handler = new AdminEmbeds($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'content-ops':
                $admin_handler = new AdminContentOps($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'capcodes':
                $admin_handler = new AdminCapcodes($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'filetype-categories':
                $admin_handler = new AdminFiletypeCategories($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'noticeboard':
                $admin_handler = new AdminNoticeboard($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            /*case 'plugins':
                $admin_handler = new AdminPlugins($this->authorization, $this->domain, $this->session);
                $admin_handler->dispatch($inputs);
                break;*/

            default:
                $this->invalidSection();
        }

        if (is_null($admin_handler)) {
            return;
        }

        if ($admin_handler->outputMain()) {
            $admin_handler->panel();
        }
    }
}