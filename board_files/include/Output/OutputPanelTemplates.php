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

    public function render(array $parameters = array(), bool $data_only = false)
    {
        $render_data = array();
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_templates_access'))
        {
            nel_derp(341, _gettext('You are not allowed to access the templates panel.'));
        }

        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $render_data['head'] = $output_head->render(['dotdot' => $dotdot]);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Templates')];
        $render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'extra_data' => $extra_data], true);
        $templates = $this->database->executeFetchAll(
                'SELECT * FROM "' . TEMPLATES_TABLE . '" ORDER BY "entry" ASC, "is_default" DESC', PDO::FETCH_ASSOC);
        $installed_ids = array();
        $bgclass = 'row1';

        foreach ($templates as $template)
        {
            $template_data = array();
            $template_info = json_decode($template['info'], true);
            $template_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $installed_ids[] = $template['id'];
            $template_data['id'] = $template['id'];
            $template_data['name'] = $template_info['name'];
            $template_data['directory'] = $template_info['directory'];
            $template_data['output'] = $template_info['output_type'];
            $template_data['is_default'] = $template['is_default'] == 1;
            $template_data['default_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'templates', 'action' => 'make-default', 'template-id' => $template['id']]);
            $template_data['remove_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'templates', 'action' => 'remove', 'template-id' => $template['id']]);
            $render_data['installed_list'][] = $template_data;
        }

        $ini_parser = new \Nelliel\INIParser($this->file_handler);
        $template_inis = $ini_parser->parseDirectories(TEMPLATES_FILE_PATH, 'template_info.ini');
        $bgclass = 'row1';

        foreach ($template_inis as $template)
        {
            $template_data = array();
            $template_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $template_data['id'] = $template['id'];
            $template_data['name'] = $template['name'];
            $template_data['directory'] = $template_info['directory'];
            $template_data['output'] = $template['output_type'];
            $template_data['is_installed'] = in_array($template['id'], $installed_ids);
            $template_data['install_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'templates', 'action' => 'add', 'template-id' => $template['id']]);
            $render_data['available_list'][] = $template_data;
        }

        $render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/templates_panel',
                $render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output($render_data, 'page', true);
        echo $output;
        return $output;
    }
}