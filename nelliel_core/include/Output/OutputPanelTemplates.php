<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

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
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Templates');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $templates = $this->domain->frontEndData()->getAllTemplates();
        $installed_ids = array();
        $bgclass = 'row1';

        foreach ($templates as $template)
        {
            $template_data = array();
            $template_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $installed_ids[] = $template->id();
            $template_data['id'] = $template->id();
            $template_data['name'] = $template->info('name');
            $template_data['directory'] = $template->info('directory');
            $template_data['output'] = $template->info('output_type');
            $template_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'templates', 'actions' => 'remove',
                                'template-id' => $template->id()]);
            $template_data['is_core'] = $this->domain->frontEndData()->templateIsCore($template->id());
            $this->render_data['installed_list'][] = $template_data;
        }

        $template_inis = $this->domain->frontEndData()->getTemplateInis();
        $bgclass = 'row1';

        foreach ($template_inis as $template)
        {
            $template_data = array();
            $template_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $template_data['id'] = $template['template-info']['id'];
            $template_data['name'] = $template['template-info']['name'];
            $template_data['directory'] = $template['template-info']['directory'];
            $template_data['output'] = $template['template-info']['output_type'];
            $template_data['is_installed'] = in_array($template['template-info']['id'], $installed_ids);
            $template_data['install_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'templates', 'actions' => 'add',
                            'template-id' => $template['template-info']['id']]);
            $this->render_data['available_list'][] = $template_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}