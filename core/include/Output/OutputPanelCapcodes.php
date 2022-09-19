<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelCapcodes extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/capcodes_main');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Capcodes');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $capcodes = $this->database->executeFetchAll('SELECT * FROM "' . NEL_CAPCODES_TABLE . '"', PDO::FETCH_ASSOC);
        $this->render_data['new_url'] = nel_build_router_url([$this->domain->id(), 'capcodes', 'new']);
        $bgclass = 'row1';

        foreach ($capcodes as $capcode) {
            $capcode_data = array();
            $capcode_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $capcode_data['capcode_id'] = $capcode['capcode_id'];
            $capcode_data['capcode'] = $capcode['capcode'];
            $capcode_data['output'] = $capcode['output'];
            $capcode_data['enabled'] = $capcode['enabled'];
            $capcode_data['edit_url'] = nel_build_router_url(
                [$this->domain->id(), 'capcodes', $capcode_data['capcode_id'], 'modify']);

            if ($capcode_data['enabled'] == 1) {
                $capcode_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->id(), 'capcodes', $capcode_data['capcode_id'], 'disable']);
                $capcode_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($capcode_data['enabled'] == 0) {
                $capcode_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->id(), 'capcodes', $capcode_data['capcode_id'], 'enable']);
                $capcode_data['enable_disable_text'] = _gettext('Enable');
            }

            $capcode_data['delete_url'] = nel_build_router_url(
                [$this->domain->id(), 'capcodes', $capcode_data['capcode_id'], 'delete']);
            $this->render_data['capcodes_list'][] = $capcode_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }

    public function new(array $parameters, bool $data_only)
    {
        $parameters['section'] = $parameters['section'] ?? _gettext('New');
        $parameters['editing'] = false;
        return $this->edit($parameters, $data_only);
    }

    public function edit(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/capcodes_edit');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Capcodes');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $editing = $parameters['editing'] ?? true;

        if ($editing) {
            $capcode_id = $parameters['capcode_id'] ?? 0;
            $form_action = nel_build_router_url([$this->domain->id(), 'capcodes', $capcode_id, 'modify']);
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_CAPCODES_TABLE . '" WHERE "capcode_id" = ?');
            $capcode_data = $this->database->executePreparedFetch($prepared, [$capcode_id], PDO::FETCH_ASSOC);

            if ($capcode_data !== false) {
                $this->render_data['capcode_id'] = $capcode_data['capcode_id'];
                $this->render_data['capcode'] = $capcode_data['capcode'];
                $this->render_data['output'] = $capcode_data['output'];
                $this->render_data['enabled'] = $capcode_data['enabled'] == 1 ? 'checked' : '';
            }
        } else {
            $form_action = nel_build_router_url([$this->domain->id(), 'capcodes', 'new']);
        }

        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}