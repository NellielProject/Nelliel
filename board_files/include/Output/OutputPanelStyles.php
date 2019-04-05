<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelStyles extends OutputCore
{

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

        if (!$user->domainPermission($this->domain, 'perm_styles_access'))
        {
            nel_derp(440, _gettext('You are not allowed to access the styles panel.'));
        }

        $this->render_core->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Styles')];
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]));
        $styles = $this->database->executeFetchAll(
                'SELECT * FROM "' . ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry" ASC, "is_default" DESC',
                PDO::FETCH_ASSOC);
        $installed_ids = array();
        $bgclass = 'row1';

        foreach ($styles as $style)
        {
            $style_data = array();
            $style_info = json_decode($style['info'], true);
            $style_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $installed_ids[] = $style['id'];
            $style_data['id'] = $style['id'];
            $style_data['style_type'] = strtoupper($style_info['style_type']);
            $style_data['name'] = $style_info['name'];
            $style_data['directory'] = $style_info['directory'];
            $style_data['is_default'] = $style['is_default'] == 1;
            $style_data['default_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'styles', 'action' => 'make-default', 'style-id' => $style['id'],
                        'style-type' => $style_info['style_type']]);
            $style_data['remove_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'styles', 'action' => 'remove', 'style-id' => $style['id'],
                        'set-type' => $style_info['style_type']]);

            $render_input['installed_list'][] = $style_data;
        }

        $ini_parser = new \Nelliel\INIParser(new \Nelliel\FileHandler());
        $style_inis = $ini_parser->parseDirectories(STYLES_WEB_PATH, 'style_info.ini');
        $bgclass = 'row1';

        foreach ($style_inis as $style)
        {
            $style_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $style_data['id'] = $style['id'];
            $style_data['style_type'] = strtoupper($style['style_type']);
            $style_data['name'] = $style['name'];
            $style_data['directory'] = $style['directory'];
            $style_data['is_installed'] = in_array($style['id'], $installed_ids);
            $style_data['install_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'styles', 'action' => 'add', 'style-id' => $style['id'],
                        'style-type' => $style['style_type']]);
            $render_input['available_list'][] = $style_data;
        }

        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/panels/styles_panel', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}