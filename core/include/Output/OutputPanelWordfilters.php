<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelWordfilters extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/wordfilters_main');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Wordfilters');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        if ($this->domain->id() === Domain::SITE) {
            $wordfilters = array();
        } else if ($this->domain->id() === Domain::GLOBAL) {
            $wordfilters = $this->database->executeFetchAll(
                'SELECT * FROM "' . NEL_WORDFILTERS_TABLE . '" ORDER BY "filter_id" DESC', PDO::FETCH_ASSOC);
        } else {
            $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_FILE_FILTERS_TABLE . '" WHERE "board_id" = ?  ORDER BY "filter_id" DESC');
            $wordfilters = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        }

        $this->render_data['new_url'] = nel_build_router_url([$this->domain->id(), 'wordfilters', 'new']);
        $bgclass = 'row1';

        foreach ($wordfilters as $wordfilter) {
            $wordfilter_data = array();
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $wordfilter_data['bgclass'] = $bgclass;
            $wordfilter_data['filter_id'] = $wordfilter['filter_id'];
            $wordfilter_data['board_id'] = $wordfilter['board_id'];
            $wordfilter_data['text_match'] = $wordfilter['text_match'];
            $wordfilter_data['replacement'] = $wordfilter['replacement'];
            $wordfilter_data['edit_url'] = nel_build_router_url(
                [$this->domain->id(), 'wordfilters', $wordfilter['filter_id'], 'modify']);

            if ($wordfilter['enabled'] == 1) {
                $wordfilter_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->id(), 'wordfilters', $wordfilter['filter_id'], 'disable']);
                $wordfilter_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($wordfilter['enabled'] == 0) {
                $wordfilter_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->id(), 'wordfilters', $wordfilter['filter_id'], 'enable']);
                $wordfilter_data['enable_disable_text'] = _gettext('Enable');
            }

            $wordfilter_data['delete_url'] = nel_build_router_url(
                [$this->domain->id(), 'wordfilters', $wordfilter['filter_id'], 'delete']);
            $this->render_data['wordfilter_list'][] = $wordfilter_data;
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
        $parameters['submit_add'] = true;
        return $this->edit($parameters, $data_only);
    }

    public function edit(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/wordfilters_edit');
        $parameters['is_panel'] = true;
        $editing = $parameters['editing'] ?? false;
        $this->render_data['submit_add'] = $parameters['submit_add'] ?? false;
        $this->render_data['submit_edit'] = $editing;
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Wordfilters');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $form_action = '';

        if ($editing) {
            $filter_id = $parameters['filter_id'] ?? 0;
            $form_action = nel_build_router_url([$this->domain->id(), 'wordfilters', $filter_id, 'modify']);
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_WORDFILTERS_TABLE . '" WHERE "filter_id" = ?');
            $wordfilter_data = $this->database->executePreparedFetch($prepared, [$filter_id], PDO::FETCH_ASSOC);

            if ($wordfilter_data !== false) {
                $this->render_data['filter_id'] = $wordfilter_data['filter_id'];
                $this->render_data['board_id'] = $wordfilter_data['board_id'];
                $this->render_data['text_match'] = $wordfilter_data['text_match'];
                $this->render_data['replacement'] = $wordfilter_data['replacement'];
                $this->render_data['is_regex_checked'] = $wordfilter_data['is_regex'] == 1 ? 'checked' : '';
                $this->render_data['enabled_checked'] = $wordfilter_data['enabled'] == 1 ? 'checked' : '';
            }
        } else {
            $form_action = nel_build_router_url([$this->domain->id(), 'wordfilters', 'new']);
        }

        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}