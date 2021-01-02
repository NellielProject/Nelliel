<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

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
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Templates');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $templates = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_TEMPLATES_TABLE . '" ORDER BY "entry" ASC, "is_default" DESC', PDO::FETCH_ASSOC);
        $installed_ids = array();
        $bgclass = 'row1';

        foreach ($templates as $template)
        {
            $template_data = array();
            $template_info = json_decode($template['info'], true);
            $template_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $installed_ids[] = $template['template_id'];
            $template_data['id'] = $template['template_id'];
            $template_data['name'] = $template_info['name'];
            $template_data['directory'] = $template_info['directory'];
            $template_data['output'] = $template_info['output_type'];
            $template_data['is_default'] = $template['is_default'] == 1;
            $template_data['default_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'templates', 'actions' => 'make-default',
                                'template-id' => $template['template_id']]);
            $template_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'templates', 'actions' => 'remove',
                                'template-id' => $template['template_id']]);
            $template_data['is_core'] = $this->domain->frontEndData()->templateIsCore($template['template_id']);
            $this->render_data['installed_list'][] = $template_data;
        }

        $template_inis = $this->domain->frontEndData()->getTemplateInis();
        $bgclass = 'row1';

        foreach ($template_inis as $template)
        {
            $template_data = array();
            $template_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $template_data['id'] = $template['id'];
            $template_data['name'] = $template['name'];
            $template_data['directory'] = $template['directory'];
            $template_data['output'] = $template['output_type'];
            $template_data['is_installed'] = in_array($template['id'], $installed_ids);
            $template_data['install_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'templates', 'actions' => 'add',
                                'template-id' => $template['id']]);
            $this->render_data['available_list'][] = $template_data;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/templates_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}