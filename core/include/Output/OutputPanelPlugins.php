<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputPanelPlugins extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/plugins');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('plugins');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $installed_plugins = nel_plugins()->getInstalledPlugins();
        $installed_ids = array();
        $bgclass = 'row1';

        foreach ($installed_plugins as $plugin) {
            $plugin_data = array();
            $plugin_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $plugin->loadData();
            $installed_ids[] = $plugin->id();
            $plugin_data['id'] = $plugin->id();
            $plugin_data['name'] = $plugin->info('name');
            $plugin_data['enabled'] = $plugin->enabled();

            if ($plugin_data['enabled'] == 1) {
                $plugin_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'plugins', $plugin->id(), 'disable']);
                $plugin_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($plugin_data['enabled'] == 0) {
                $plugin_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'plugins', $plugin->id(), 'enable']);
                $plugin_data['enable_disable_text'] = _gettext('Enable');
            }

            $plugin_data['uninstall_url'] = nel_build_router_url(
                [$this->domain->uri(), 'plugins', $plugin->id(), 'uninstall']);
            $this->render_data['installed_list'][] = $plugin_data;
        }

        $plugins = nel_plugins()->getAvailablePlugins();
        $bgclass = 'row1';

        foreach ($plugins as $plugin) {
            $plugin_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $plugin_data['id'] = $plugin->id();
            $plugin_data['name'] = $plugin->info('name');
            $plugin_data['is_installed'] = in_array($plugin->id(), $installed_ids);
            $plugin_data['install_url'] = nel_build_router_url(
                [$this->domain->uri(), 'plugins', $plugin->id(), 'install']);
            $this->render_data['available_list'][] = $plugin_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}