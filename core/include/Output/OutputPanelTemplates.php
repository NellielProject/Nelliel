<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputPanelTemplates extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/templates');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Templates');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $templates = $this->domain->frontEndData()->getAllTemplates(false);
        $installed_ids = array();
        $bgclass = 'row1';

        foreach ($templates as $template) {
            $template_data = array();
            $template_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $template->load();
            $installed_ids[] = $template->id();
            $template_data['id'] = $template->id();
            $template_data['name'] = $template->info('name');
            $template_data['directory'] = $template->info('directory');
            $template_data['output'] = $template->info('output_type');
            $template_data['enabled'] = $template->enabled();

            if ($template_data['enabled'] == 1) {
                $template_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'templates', $template->id(), 'disable']);
                $template_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($template_data['enabled'] == 0) {
                $template_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'templates', $template->id(), 'enable']);
                $template_data['enable_disable_text'] = _gettext('Enable');
            }
            $template_data['uninstall_url'] = nel_build_router_url(
                [$this->domain->uri(), 'templates', $template->id(), 'uninstall']);
            $this->render_data['installed_list'][] = $template_data;
        }

        $template_inis = $this->domain->frontEndData()->getTemplateInis();
        $bgclass = 'row1';

        foreach ($template_inis as $template) {
            $template_data = array();
            $template_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';

            $template_data['id'] = $template['info']['id'];
            $template_data['name'] = $template['info']['name'];
            $template_data['output'] = $template['info']['output_type'];
            $template_data['is_installed'] = in_array($template['info']['id'], $installed_ids);
            $template_data['install_url'] = nel_build_router_url(
                [$this->domain->uri(), 'templates', $template['info']['id'], 'install']);
            $this->render_data['available_list'][] = $template_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}