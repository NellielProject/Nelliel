<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelScripts extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/scripts_main');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Scripts');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['new_url'] = nel_build_router_url([$this->domain->id(), 'scripts', 'new']);
        $scripts = $this->database->executeFetchAll('SELECT * FROM "' . NEL_SCRIPTS_TABLE . '"',
            PDO::FETCH_ASSOC);
        $bgclass = 'row1';

        foreach ($scripts as $script) {
            $script_data = array();
            $script_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $script_data['script_id'] = $script['script_id'];
            $script_data['label'] = $script['label'];
            $script_data['location'] = $script['location'];
            $script_data['full_url'] = $script['full_url'];
            $script_data['enabled'] = $script['enabled'];
            $script_data['notes'] = $script['notes'];
            $script_data['edit_url'] = nel_build_router_url(
                [$this->domain->id(), 'scripts', $script_data['script_id'], 'modify']);

            if ($script_data['enabled'] == 1) {
                $script_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->id(), 'scripts', $script_data['script_id'], 'disable']);
                $script_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($script_data['enabled'] == 0) {
                $script_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->id(), 'scripts', $script_data['script_id'], 'enable']);
                $script_data['enable_disable_text'] = _gettext('Enable');
            }

            $script_data['delete_url'] = nel_build_router_url(
                [$this->domain->id(), 'scripts', $script_data['script_id'], 'delete']);
            $this->render_data['scripts_list'][] = $script_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
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
        $this->setBodyTemplate('panels/scripts_edit');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Scripts');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $editing = $parameters['editing'] ?? true;

        if ($editing) {
            $script_id = $parameters['script_id'] ?? '';
            $form_action = nel_build_router_url([$this->domain->id(), 'scripts', $script_id, 'modify']);
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_SCRIPTS_TABLE . '" WHERE "script_id" = ?');
            $script_data = $this->database->executePreparedFetch($prepared, [$script_id], PDO::FETCH_ASSOC);

            if ($script_data !== false) {
                $this->render_data['script_id'] = $script_data['script_id'];
                $this->render_data['label'] = $script_data['label'];
                $this->render_data['location'] = $script_data['location'];
                $this->render_data['full_url'] = $script_data['full_url'] == 1 ? 'checked' : '';
                $this->render_data['enabled'] = $script_data['enabled'] == 1 ? 'checked' : '';
                $this->render_data['notes'] = $script_data['notes'];
            }
        } else {
            $form_action = nel_build_router_url([$this->domain->id(), 'scripts', 'new']);
        }

        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}