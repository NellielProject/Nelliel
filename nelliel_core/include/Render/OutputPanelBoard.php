<?php

declare(strict_types=1);

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;

class OutputPanelBoard extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/board');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Main');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $board_id = $parameters['board_id'] ?? '';
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['module_board_settings'] = $this->session->user()->checkPermission($this->domain,
                'perm_manage_board_config');
        $this->render_data['board_settings_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=admin&section=board-settings&board-id=' . $board_id;
        $this->render_data['module_bans'] = $this->session->user()->checkPermission($this->domain,
                'perm_manage_bans');
        $this->render_data['bans_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=bans&board-id=' .
                $board_id;
        //$this->render_data['module_threads'] = true;
        //$this->render_data['threads_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=threads&board-id=' . $this->domain->id();
        $this->render_data['module_modmode'] = $this->session->user()->checkPermission($this->domain,
                'perm_mod_mode');
        $this->render_data['modmode_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=render&actions=view-index&index=0&board-id=' . $board_id . '&modmode=true';
        $this->render_data['module_reports'] = $this->session->user()->checkPermission($this->domain,
                'perm_manage_reports');
        $this->render_data['reports_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=reports&board-id=' .
                $board_id;
        $this->render_data['module_file_filters'] = $this->session->user()->checkPermission($this->domain,
                'perm_manage_file_filters');
        $this->render_data['file_filters_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=admin&section=file-filters&board-id=' . $board_id;
        $this->render_data['module_ifthens'] = $this->session->user()->checkPermission($this->domain,
                'perm_manage_ifthens');
        $this->render_data['ifthens_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
        'module=admin&section=ifthens&board-id=' . $board_id;
        $this->render_data['regen_board_pages'] = $this->session->user()->checkPermission($this->domain,
                'perm_regen_pages');
        $this->render_data['regen_pages_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=regen&actions=board-all-pages&board-id=' . $board_id;
        $this->render_data['regen_board_caches'] = $this->session->user()->checkPermission($this->domain,
                'perm_regen_cache');
        $this->render_data['regen_caches_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=regen&actions=board-all-caches&board-id=' . $board_id;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}