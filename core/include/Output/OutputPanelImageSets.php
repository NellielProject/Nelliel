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
        $this->setBodyTemplate('panels/image_sets');
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
                $set_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'image-sets', $image_set->id(), 'disable']);
                $set_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($set_data['enabled'] == 0) {
                $set_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'image-sets', $image_set->id(), 'enable']);
                $set_data['enable_disable_text'] = _gettext('Enable');
            }

            $set_data['uninstall_url'] = nel_build_router_url(
                [$this->domain->uri(), 'image-sets', $image_set->id(), 'uninstall']);
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
            $set_data['is_installed'] = in_array($image_set['info']['id'], $installed_ids);
            $set_data['install_url'] = nel_build_router_url(
                [$this->domain->uri(), 'image-sets', $image_set['info']['id'], 'install']);
            $this->render_data['available_list'][] = $set_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}