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

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $user = $parameters['user'];

        if (!$user->checkPermission($this->domain, 'perm_manage_styles'))
        {
            nel_derp(440, _gettext('You are not allowed to manage styles.'));
        }

        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Styles')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $styles = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_ASSETS_TABLE . '" WHERE "type" = \'style\' ORDER BY "entry" ASC, "is_default" DESC',
                PDO::FETCH_ASSOC);
        $installed_ids = array();
        $bgclass = 'row1';

        foreach ($styles as $style)
        {
            $style_data = array();
            $style_info = json_decode($style['info'], true);
            $style_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $installed_ids[] = $style['asset_id'];
            $style_data['id'] = $style['asset_id'];
            $style_data['style_type'] = strtoupper($style_info['style_type']);
            $style_data['name'] = $style_info['name'];
            $style_data['directory'] = $style_info['directory'];
            $style_data['is_default'] = $style['is_default'] == 1;
            $style_data['default_url'] = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                    ['module' => 'styles', 'action' => 'make-default', 'style-id' => $style['asset_id'],
                        'style-type' => $style_info['style_type']]);
            $style_data['remove_url'] = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                    ['module' => 'styles', 'action' => 'remove', 'style-id' => $style['asset_id'],
                        'set-type' => $style_info['style_type']]);
            $style_data['is_core'] = $this->domain->frontEndData()->styleIsCore($style['asset_id']);

            $this->render_data['installed_list'][] = $style_data;
        }

        $front_end_data = new \Nelliel\FrontEndData($this->database);
        $style_inis = $front_end_data->getStyleInis();
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
            $style_data['install_url'] = $this->url_constructor->dynamic(NEL_MAIN_SCRIPT,
                    ['module' => 'styles', 'action' => 'add', 'style-id' => $style['id'],
                        'style-type' => $style['style_type']]);
            $this->render_data['available_list'][] = $style_data;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/styles_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}