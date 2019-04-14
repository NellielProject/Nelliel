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

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->startTimer();
        $session = new \Nelliel\Session(true);
        $user = $session->sessionUser();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Main Panel')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $this->render_data['module_board_settings'] = $user->domainPermission($this->domain, 'perm_board_config_access');
        $this->render_data['board_settings_url'] = MAIN_SCRIPT . '?module=board-settings&board_id=' . $this->domain->id();
        $this->render_data['module_bans'] = $user->domainPermission($this->domain, 'perm_ban_access');
        $this->render_data['bans_url'] = MAIN_SCRIPT . '?module=bans&board_id=' . $this->domain->id();
        $this->render_data['module_threads'] = $user->domainPermission($this->domain, 'perm_threads_access');
        $this->render_data['threads_url'] = MAIN_SCRIPT . '?module=threads-admin&board_id=' . $this->domain->id();
        $this->render_data['module_modmode'] = $user->domainPermission($this->domain, 'perm_modmode_access');
        $this->render_data['modmode_url'] = MAIN_SCRIPT . '?module=render&action=view-index&index=0&board_id=' .
                $this->domain->id() . '&modmode=true';
        $this->render_data['module_reports'] = $user->domainPermission($this->domain, 'perm_reports_access');
        $this->render_data['reports_url'] = MAIN_SCRIPT . '?module=reports&board_id=' . $this->domain->id();
        $this->render_data['module_file_filters'] = $user->domainPermission($this->domain, 'perm_file_filters_access');
        $this->render_data['file_filters_url'] = MAIN_SCRIPT . '?module=file-filters&board_id=' . $this->domain->id();
        $this->render_data['regen_board_pages'] = $user->domainPermission($this->domain, 'perm_regen_pages');
        $this->render_data['regen_pages_url'] = MAIN_SCRIPT . '?module=regen&action=board-all-pages&board_id=' .
                $this->domain->id();
        $this->render_data['regen_board_caches'] = $user->domainPermission($this->domain, 'perm_regen_cache');
        $this->render_data['regen_caches_url'] = MAIN_SCRIPT . '?module=regen&action=board-all-caches&board_id=' .
                $this->domain->id();
        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/board_panel',
                $this->render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}