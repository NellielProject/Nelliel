<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputPanelStyles extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/styles');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Styles');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $styles = $this->domain->frontEndData()->getAllStyles(false);
        $installed_ids = array();
        $bgclass = 'row1';

        foreach ($styles as $style) {
            $style_data = array();
            $style_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $style->load();
            $installed_ids[] = $style->id();
            $style_data['id'] = $style->id();
            $style_data['style_type'] = strtoupper($style->info('style_type'));
            $style_data['name'] = $style->info('name');
            $style_data['directory'] = $style->info('directory');
            $style_data['enabled'] = $style->enabled();

            if ($style_data['enabled'] == 1) {
                $style_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                        ['module' => 'admin', 'section' => 'styles', 'actions' => 'disable', 'style-id' => $style->id()]);
                $style_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($style_data['enabled'] == 0) {
                $style_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                        ['module' => 'admin', 'section' => 'styles', 'actions' => 'enable', 'style-id' => $style->id()]);
                $style_data['enable_disable_text'] = _gettext('Enable');
            }

            $style_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'styles', 'actions' => 'remove', 'style-id' => $style->id()]);
            $this->render_data['installed_list'][] = $style_data;
        }

        $style_inis = $this->domain->frontEndData()->getAllStyles(false);
        $bgclass = 'row1';

        foreach ($style_inis as $style) {
            $style_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $style_data['id'] = $style->id();
            $style_data['style_type'] = strtoupper($style->info('style_type'));
            $style_data['name'] = $style->info('name');
            $style_data['directory'] = $style->info('directory');
            $style_data['is_installed'] = in_array($style->id(), $installed_ids);
            $style_data['install_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'styles', 'actions' => 'add',
                        'style-id' => $style->id(), 'style-type' => $style->info('style_type')]);
            $this->render_data['available_list'][] = $style_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}