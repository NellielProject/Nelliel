<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelIconSets extends OutputCore
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

        if (!$user->checkPermission($this->domain, 'perm_manage_icon_sets'))
        {
            nel_derp(460, _gettext('You are not allowed to manage icon sets.'));
        }

        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Icon Sets')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);
        $icon_sets = $this->database->executeFetchAll(
                'SELECT * FROM "' . ASSETS_TABLE .
                '" WHERE "type" = \'icon-set\' ORDER BY "entry" ASC, "is_default" DESC', PDO::FETCH_ASSOC);
        $installed_ids = array();
        $bgclass = 'row1';

        foreach ($icon_sets as $icon_set)
        {
            $set_data = array();
            $icon_set_info = json_decode($icon_set['info'], true);
            $set_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $installed_ids[] = $icon_set['id'];
            $set_data['id'] = $icon_set['id'];
            $set_data['set_type'] = strtoupper($icon_set_info['set_type']);
            $set_data['name'] = $icon_set_info['name'];
            $set_data['directory'] = $icon_set_info['directory'];
            $set_data['is_default'] = $icon_set['is_default'] == 1;
            $set_data['default_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'icon-sets', 'action' => 'make-default', 'icon-set-id' => $icon_set['id'],
                        'set-type' => $icon_set_info['set_type']]);
            $set_data['remove_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'icon-sets', 'action' => 'remove', 'icon-set-id' => $icon_set['id'],
                        'set-type' => $icon_set_info['set_type']]);
            $set_data['is_core'] = $this->domain->getFrontEndData()->iconSetIsCore($icon_set['id']);

            $this->render_data['installed_list'][] = $set_data;
        }

        $front_end_data = new \Nelliel\FrontEndData($this->database);
        $icon_set_inis = $front_end_data->getIconSetInis();
        $bgclass = 'row1';

        foreach ($icon_set_inis as $icon_set)
        {
            $set_data = array();
            $set_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $set_data['id'] = $icon_set['id'];
            $set_data['set_type'] = strtoupper($icon_set_info['set_type']);
            $set_data['name'] = $icon_set_info['name'];
            $set_data['directory'] = $icon_set_info['directory'];
            $set_data['is_installed'] = in_array($icon_set['id'], $installed_ids);
            $set_data['install_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'icon-sets', 'action' => 'add', 'icon-set-id' => $icon_set['id'],
                        'set-type' => $icon_set['set_type']]);
            $this->render_data['available_list'][] = $set_data;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/icon_sets_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}