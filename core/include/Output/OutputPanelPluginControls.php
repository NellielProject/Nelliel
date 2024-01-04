<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Render\Template;

class OutputPanelPluginControls extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/main_plugin_controls');
        $parameters['panel'] = $parameters['panel'] ?? __('Plugin Controls');
        $parameters['section'] = $parameters['section'] ?? __('Main');
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

    public function plugin(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $plugin_id = $parameters['plugin_id'] ?? '';
        $plugin = nel_plugins()->getPlugin($plugin_id);
        $parameters['panel'] = $parameters['panel'] ?? __('Plugin Controls');
        $parameters['section'] = $plugin->info('name');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $base_path = $this->templates_path;
        $body_template = 'panels/main_plugin_controls';
        $render_data = nel_plugins()->processHook('nel-inb4-plugin-controls-render', [$this->domain, $plugin_id, &$base_path, &$body_template], array());
        $this->render_data = array_merge($this->render_data, $render_data);
        $template = new Template($base_path, $body_template, '.html');
        $this->render_core->renderEngine()->getLoader()->addSubstitute($this->default_body_template, $template);
        $output = $this->output('basic_page', $data_only, false, $this->render_data);
        echo $output;
        return $output;
    }
}