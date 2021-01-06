<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelIconSets extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/icon_sets');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Icon Sets');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $icon_sets = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_ASSETS_TABLE .
                '" WHERE "type" = \'icon-set\' ORDER BY "entry" ASC, "is_default" DESC', PDO::FETCH_ASSOC);
        $installed_ids = array();
        $bgclass = 'row1';

        foreach ($icon_sets as $icon_set)
        {
            $set_data = array();
            $icon_set_info = json_decode($icon_set['info'], true);
            $set_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $installed_ids[] = $icon_set['asset_id'];
            $set_data['id'] = $icon_set['asset_id'];
            $set_data['name'] = $icon_set_info['name'];
            $set_data['directory'] = $icon_set_info['directory'];
            $set_data['is_default'] = $icon_set['is_default'] == 1;
            $set_data['default_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'icon-sets', 'actions' => 'make-default',
                                'icon-set-id' => $icon_set['asset_id']]);
            $set_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'icon-sets', 'actions' => 'remove',
                                'icon-set-id' => $icon_set['asset_id']]);
            $set_data['is_core'] = $this->domain->frontEndData()->iconSetIsCore($icon_set['asset_id']);
            $this->render_data['installed_list'][] = $set_data;
        }

        $icon_set_inis = $this->domain->frontEndData()->getIconSetInis();
        $bgclass = 'row1';

        foreach ($icon_set_inis as $icon_set)
        {
            $set_data = array();
            $set_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $set_data['id'] = $icon_set['id'];
            $set_data['name'] = $icon_set_info['name'];
            $set_data['directory'] = $icon_set_info['directory'];
            $set_data['is_installed'] = in_array($icon_set['id'], $installed_ids);
            $set_data['install_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'icon-sets', 'actions' => 'add',
                                'icon-set-id' => $icon_set['id']]);
            $this->render_data['available_list'][] = $set_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}