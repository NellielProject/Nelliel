<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelTemplates extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $user = $parameters['user'];

        if (!$user->checkPermission($this->domain, 'perm_manage_templates'))
        {
            nel_derp(420, _gettext('You are not allowed to manage templates.'));
        }

        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Templates')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
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
            $template_data['default_url'] = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                    ['module' => 'templates', 'action' => 'make-default', 'template-id' => $template['template_id']]);
            $template_data['remove_url'] = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                    ['module' => 'templates', 'action' => 'remove', 'template-id' => $template['template_id']]);
            $this->render_data['installed_list'][] = $template_data;
        }

        $front_end_data = new \Nelliel\FrontEndData($this->database);
        $template_inis = $front_end_data->getTemplateInis();
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
            $template_data['install_url'] = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                    ['module' => 'templates', 'action' => 'add', 'template-id' => $template['id']]);
            $template_data['is_core'] = $this->domain->frontEndData()->templateIsCore($template['id']);

            $this->render_data['available_list'][] = $template_data;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/templates_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}