<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

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
            'perm_board_config_modify');
        $this->render_data['board_settings_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
            'module=admin&section=board-settings&board-id=' . $board_id;
        $this->render_data['module_bans'] = $this->session->user()->checkPermission($this->domain, 'perm_bans_view');
        $this->render_data['bans_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=bans&board-id=' .
            $board_id;
        // $this->render_data['module_threads'] = true;
        // $this->render_data['threads_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=threads&board-id=' . $this->domain->id();
        $this->render_data['module_modmode'] = $this->session->user()->checkPermission($this->domain, 'perm_mod_mode');
        $this->render_data['modmode_url'] = nel_build_router_url([$this->domain->id()], true, 'modmode');
        $this->render_data['module_reports'] = $this->session->user()->checkPermission($this->domain,
            'perm_reports_view');
        $this->render_data['reports_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=admin&section=reports&board-id=' .
            $board_id;
        $this->render_data['module_file_filters'] = $this->session->user()->checkPermission($this->domain,
            'perm_file_filters_manage');
        $this->render_data['file_filters_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
            'module=admin&section=file-filters&board-id=' . $board_id;
        $this->render_data['module_word_filters'] = $this->session->user()->checkPermission($this->domain,
            'perm_word_filters_manage');
        $this->render_data['word_filters_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
            'module=admin&section=word-filters&board-id=' . $board_id;
        $this->render_data['regen_board_pages'] = $this->session->user()->checkPermission($this->domain,
            'perm_regen_pages');
        $this->render_data['regen_pages_url'] = nel_build_router_url([$this->domain->id(), 'regen', 'pages']);
        $this->render_data['regen_board_caches'] = $this->session->user()->checkPermission($this->domain,
            'perm_regen_cache');
        $this->render_data['regen_caches_url'] = nel_build_router_url([$this->domain->id(), 'regen', 'cache']);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}