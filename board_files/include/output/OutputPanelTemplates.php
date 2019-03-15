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
        $this->database = $domain->database();
        $this->domain = $domain;
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_templates_access'))
        {
            nel_derp(341, _gettext('You are not allowed to access the templates panel.'));
        }

        $this->prepare('management/templates_panel.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Templates')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $ini_parser = new \Nelliel\INIParser($this->file_handler);
        $template_inis = $ini_parser->parseDirectories(TEMPLATES_FILE_PATH, 'template_info.ini');

        $templates = $this->database->executeFetchAll(
                'SELECT * FROM "' . TEMPLATES_TABLE . '" ORDER BY "entry" ASC, "is_default" DESC', PDO::FETCH_ASSOC);
        $installed_ids = array();
        $installed_template_list = $this->dom->getElementById('installed-template-list');
        $installed_template_list_nodes = $installed_template_list->getElementsByAttributeName('data-parse-id', true);
        $bgclass = 'row1';

        foreach ($templates as $template)
        {
            $template_info = json_decode($template['info'], true);
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $installed_ids[] = $template['id'];
            $template_row = $this->dom->copyNode($installed_template_list_nodes['template-row'], $installed_template_list,
                    'append');
            $template_row->extSetAttribute('class', $bgclass);
            $template_row_nodes = $template_row->getElementsByAttributeName('data-parse-id', true);
            $template_row_nodes['id']->setContent($template['id']);
            $template_row_nodes['name']->setContent($template_info['name']);
            $template_row_nodes['directory']->setContent($template_info['directory']);
            $template_row_nodes['output']->setContent($template_info['output_type']);

            if ($template['is_default'] == 1)
            {
                $template_row_nodes['default-link']->remove();
                $template_row_nodes['remove-link']->remove();
                $template_row_nodes['action-1']->setContent(_gettext('Default Template'));
            }
            else
            {
                $default_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'templates', 'action' => 'make-default', 'template-id' => $template['id']]);
                $template_row_nodes['template-default-link']->extSetAttribute('href', $default_link);
                $remove_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'templates', 'action' => 'remove', 'template-id' => $template['id']]);
                $template_row_nodes['remove-link']->extSetAttribute('href', $remove_link);
            }
        }

        $installed_template_list_nodes['template-row']->remove();

        $available_template_list = $this->dom->getElementById('available-template-list');
        $available_template_list_nodes = $available_template_list->getElementsByAttributeName('data-parse-id', true);
        $bgclass = 'row1';

        foreach ($template_inis as $template)
        {
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $template_row = $this->dom->copyNode($available_template_list_nodes['template-row'], $available_template_list,
                    'append');
            $template_row->extSetAttribute('class', $bgclass);
            $template_row_nodes = $template_row->getElementsByAttributeName('data-parse-id', true);
            $template_row_nodes['id']->setContent($template['id']);
            $template_row_nodes['name']->setContent($template['name']);
            $template_row_nodes['directory']->setContent($template_info['directory']);
            $template_row_nodes['output']->setContent($template['output_type']);

            if (in_array($template['id'], $installed_ids))
            {
                $template_row_nodes['install-link']->remove();
                $template_row_nodes['action-1']->setContent(_gettext('Template Installed'));
            }
            else
            {
                $remove_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'templates', 'action' => 'add', 'template-id' => $template['id']]);
                $template_row_nodes['install-link']->extSetAttribute('href', $remove_link);
            }
        }

        $available_template_list_nodes['template-row']->remove();
        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}