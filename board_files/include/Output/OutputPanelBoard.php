<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputPanelBoard extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $this->render_core->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Options')];
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]));
        $session = new \Nelliel\Session(true);
        $user = $session->sessionUser();
        $render_input['module_board_settings'] = $user->domainPermission($this->domain, 'perm_board_config_access');
        $render_input['board_settings_url'] = MAIN_SCRIPT . '?module=board-settings&board_id=' . $this->domain->id();
        $render_input['module_bans'] = $user->domainPermission($this->domain, 'perm_ban_access');
        $render_input['bans_url'] = MAIN_SCRIPT . '?module=bans&board_id=' . $this->domain->id();
        $render_input['module_threads'] = $user->domainPermission($this->domain, 'perm_threads_access');
        $render_input['threads_url'] = MAIN_SCRIPT . '?module=threads-admin&board_id=' . $this->domain->id();
        $render_input['module_modmode'] = $user->domainPermission($this->domain, 'perm_modmode_access');
        $render_input['modmode_url'] = MAIN_SCRIPT . '?module=render&action=view-index&index=0&board_id=' .
                $this->domain->id() . '&modmode=true';
        $render_input['module_reports'] = $user->domainPermission($this->domain, 'perm_reports_access');
        $render_input['reports_url'] = MAIN_SCRIPT . '?module=reports&board_id=' . $this->domain->id();
        $render_input['module_file_filters'] = $user->domainPermission($this->domain, 'perm_file_filters_access');
        $render_input['file_filters_url'] = MAIN_SCRIPT . '?module=file-filters&board_id=' . $this->domain->id();
        $render_input['regen_board_pages'] = $user->domainPermission($this->domain, 'perm_regen_pages');
        $render_input['regen_pages_url'] = MAIN_SCRIPT . '?module=regen&action=board-all-pages&board_id=' .
                $this->domain->id();
        $render_input['regen_board_caches'] = $user->domainPermission($this->domain, 'perm_regen_cache');
        $render_input['regen_caches_url'] = MAIN_SCRIPT . '?module=regen&action=board-all-caches&board_id=' .
                $this->domain->id();
        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/panels/board_panel', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}