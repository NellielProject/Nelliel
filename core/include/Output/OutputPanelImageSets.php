<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputPanelImageSets extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/image_sets');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Image Sets');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $image_sets = $this->domain->frontEndData()->getAllImageSets(false);
        $installed_ids = array();
        $bgclass = 'row1';

        foreach ($image_sets as $image_set) {
            $set_data = array();
            $set_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $image_set->load();
            $installed_ids[] = $image_set->id();
            $set_data['id'] = $image_set->id();
            $set_data['name'] = $image_set->info('name');
            $set_data['directory'] = $image_set->info('directory');
            $set_data['enabled'] = $image_set->enabled();

            if ($set_data['enabled'] == 1) {
                $set_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                        ['module' => 'admin', 'section' => 'image-sets', 'actions' => 'disable',
                            'image-set-id' => $image_set->id()]);
                $set_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($set_data['enabled'] == 0) {
                $set_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                        ['module' => 'admin', 'section' => 'image-sets', 'actions' => 'enable',
                            'image-set-id' => $image_set->id()]);
                $set_data['enable_disable_text'] = _gettext('Enable');
            }

            $set_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'image-sets', 'actions' => 'remove',
                        'image-set-id' => $image_set->id()]);
            $this->render_data['installed_list'][] = $set_data;
        }

        $image_set_inis = $this->domain->frontEndData()->getImageSetInis();
        $bgclass = 'row1';

        foreach ($image_set_inis as $image_set) {
            $set_data = array();
            $set_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $set_data['id'] = $image_set['info']['id'];
            $set_data['name'] = $image_set['info']['name'];
            $set_data['directory'] = $image_set['info']['directory'];
            $set_data['is_installed'] = in_array($image_set['info']['id'], $installed_ids);
            $set_data['install_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'image-sets', 'actions' => 'add',
                        'image-set-id' => $image_set['info']['id']]);
            $this->render_data['available_list'][] = $set_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}