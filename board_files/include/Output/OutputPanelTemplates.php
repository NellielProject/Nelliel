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
    private $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_templates_access'))
        {
            nel_derp(341, _gettext('You are not allowed to access the templates panel.'));
        }

        $this->render_core->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Templates')];
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]));
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
            $render_input['installed_list'][] = $template_data;
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
            $render_input['available_list'][] = $template_data;
        }

        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/panels/templates_panel', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}