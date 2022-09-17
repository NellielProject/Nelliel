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
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['module_board_config'] = $this->session->user()->checkPermission($this->domain,
            'perm_board_config_modify');
        $this->render_data['board_config_url'] = nel_build_router_url([$this->domain->id(), 'config']);
        $this->render_data['module_bans'] = $this->session->user()->checkPermission($this->domain, 'perm_bans_view');
        $this->render_data['bans_url'] = nel_build_router_url([$this->domain->id(), 'bans']);
        // $this->render_data['module_threads'] = true;
        // $this->render_data['threads_url'] = nel_build_router_url([$this->domain->id(), 'threads']);
        $this->render_data['module_modmode'] = $this->session->user()->checkPermission($this->domain, 'perm_mod_mode');
        $this->render_data['modmode_url'] = nel_build_router_url([$this->domain->id()], true, 'modmode');
        $this->render_data['module_reports'] = $this->session->user()->checkPermission($this->domain,
            'perm_reports_view');
        $this->render_data['reports_url'] = nel_build_router_url([$this->domain->id(), 'reports']);
        $this->render_data['module_file_filters'] = $this->session->user()->checkPermission($this->domain,
            'perm_file_filters_manage');
        $this->render_data['file_filters_url'] = nel_build_router_url([$this->domain->id(), 'file-filters']);
        $this->render_data['module_wordfilters'] = $this->session->user()->checkPermission($this->domain,
            'perm_word_filters_manage');
        $this->render_data['wordfilters_url'] = nel_build_router_url([$this->domain->id(), 'wordfilters']);
        $this->render_data['module_pages'] = $this->session->user()->checkPermission($this->domain, 'perm_pages_manage');
        $this->render_data['pages_url'] = nel_build_router_url([$this->domain->id(), 'pages']);
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