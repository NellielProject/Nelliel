<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputPanelMain extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function site(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/main_site');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Main');
        $parameters['section'] = $parameters['section'] ?? _gettext('Site');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_boards')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'manage-boards']);
            $info['name'] = __('Manage Boards');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_modify_site_config')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'config']);
            $info['name'] = __('Site Config');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_modify_board_defaults')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'board-defaults']);
            $info['name'] = __('Board Defaults');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_view_users')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'users']);
            $info['name'] = __('Site Config');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_view_roles')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'roles']);
            $info['name'] = __('Roles');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_permissions')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'permissions']);
            $info['name'] = __('Permissions');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_styles')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'styles']);
            $info['name'] = __('Styles');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_templates')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'templates']);
            $info['name'] = __('Templates');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_image_sets')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'image-sets']);
            $info['name'] = __('Image Sets');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_filetypes')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'filetypes']);
            $info['name'] = __('Filetypes');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_news')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'news']);
            $info['name'] = __('News');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_blotter')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'blotter']);
            $info['name'] = __('Blotter');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_pages')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'pages']);
            $info['name'] = __('Static Pages');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_embeds')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'embeds']);
            $info['name'] = __('Embeds');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_content_ops')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'content-ops']);
            $info['name'] = __('Content Ops');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_capcodes')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'capcodes']);
            $info['name'] = __('Capcodes');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_noticeboard')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'noticeboard']);
            $info['name'] = __('Noticeboard');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_markup')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'markup']);
            $info['name'] = __('Markup');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_scripts')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'scripts']);
            $info['name'] = __('Scripts');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_logs')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'logs']);
            $info['name'] = __('Logs');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_plugins')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'plugins']);
            $info['name'] = __('Plugins');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_access_plugin_controls')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'plugin-controls']);
            $info['name'] = __('Plugin Controls');
            $this->render_data['control_panels'][] = $info;
        }

        $this->render_data['regen_overboard_pages'] = $this->session->user()->checkPermission($this->domain,
            'perm_regen_overboard');
        $this->render_data['regen_overboard_url'] = nel_build_router_url([$this->domain->uri(), 'regen', 'overboard']);

        $this->render_data['regen_site_pages'] = $this->session->user()->checkPermission($this->domain,
            'perm_regen_pages');
        $this->render_data['regen_site_pages_url'] = nel_build_router_url([$this->domain->uri(), 'regen', 'pages']);

        $this->render_data['regen_site_caches'] = $this->session->user()->checkPermission($this->domain,
            'perm_regen_cache');
        $this->render_data['regen_site_caches_url'] = nel_build_router_url([$this->domain->uri(), 'regen', 'cache']);

        $this->render_data['module_extract_gettext'] = $this->session->user()->checkPermission($this->domain,
            'perm_extract_gettext');
        $this->render_data['extract_gettext_url'] = nel_build_router_url(
            [$this->domain->uri(), 'language', 'gettext', 'extract']);

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function global(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/main_global');
        $parameters['panel'] = $parameters['panel'] ?? __('Main');
        $parameters['section'] = $parameters['section'] ?? __('Global');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        if ($this->session->user()->checkPermission($this->domain, 'perm_view_bans')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'bans']);
            $info['name'] = __('Ban Controls');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_view_reports')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'reports']);
            $info['name'] = __('Reports');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_file_filters')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'file-filters']);
            $info['name'] = __('File Filters');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_wordfilters')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'wordfilters']);
            $info['name'] = __('Wordilters');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_pages')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'pages']);
            $info['name'] = __('Static Pages');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_access_plugin_controls')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'plugin-controls']);
            $info['name'] = __('Plugin Controls');
            $this->render_data['control_panels'][] = $info;
        }

        $this->render_data['global_regen_board_pages'] = $this->session->user()->checkPermission($this->domain,
            'perm_regen_pages');
        $this->render_data['global_regen_board_pages_url'] = nel_build_router_url(
            [$this->domain->uri(), 'regen', 'pages']);

        $this->render_data['global_regen_board_caches'] = $this->session->user()->checkPermission($this->domain,
            'perm_regen_cache');
        $this->render_data['global_regen_board_caches_url'] = nel_build_router_url(
            [$this->domain->uri(), 'regen', 'cache']);

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function board(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/main_board');
        $parameters['panel'] = $parameters['panel'] ?? __('Main');
        $parameters['section'] = $parameters['section'] ?? __('Board');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        if ($this->session->user()->checkPermission($this->domain, 'perm_view_bans')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'bans']);
            $info['name'] = __('Ban Controls');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_view_reports')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'reports']);
            $info['name'] = __('Reports');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_file_filters')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'file-filters']);
            $info['name'] = __('File Filters');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_wordfilters')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'wordfilters']);
            $info['name'] = __('Wordilters');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_manage_pages')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'pages']);
            $info['name'] = __('Static Pages');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_modify_board_config')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'config']);
            $info['name'] = __('Board Config');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_access_plugin_controls')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'plugin-controls']);
            $info['name'] = __('Plugin Controls');
            $this->render_data['control_panels'][] = $info;
        }

        if ($this->session->user()->checkPermission($this->domain, 'perm_mod_mode')) {
            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri()], true, 'modmode');
            $info['name'] = __('Moderator Mode');
            $this->render_data['control_panels'][] = $info;
        }

        // $this->render_data['module_threads'] = true;
        // $this->render_data['threads_url'] = nel_build_router_url([$this->domain->uri(), 'threads']);

        $this->render_data['regen_board_pages'] = $this->session->user()->checkPermission($this->domain,
            'perm_regen_pages');
        $this->render_data['regen_pages_url'] = nel_build_router_url([$this->domain->uri(), 'regen', 'pages']);

        $this->render_data['regen_board_caches'] = $this->session->user()->checkPermission($this->domain,
            'perm_regen_cache');
        $this->render_data['regen_caches_url'] = nel_build_router_url([$this->domain->uri(), 'regen', 'cache']);

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function plugin_controls(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/main_plugin_controls');
        $parameters['panel'] = $parameters['panel'] ?? __('Main');
        $parameters['section'] = $parameters['section'] ?? __('Plugin Controls');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $plugin_ids = nel_plugins()->processHook('nel-inb4-plugin-controls-list', [$this->domain], array());

        foreach ($plugin_ids as $plugin_id) {
            $plugin = nel_plugins()->getPlugin($plugin_id);

            if (!$plugin->enabled()) {
                continue;
            }

            $info = array();
            $info['url'] = nel_build_router_url([$this->domain->uri(), 'plugin-controls', $plugin_id]);
            $info['name'] = $plugin->info('name');
            $this->render_data['plugins'][] = $info;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}