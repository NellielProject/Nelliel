<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Filters\Filters;
use Nelliel\Filters\Wordfilter;

class OutputPanelWordfilters extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function main(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/wordfilters_main');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Wordfilters');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['new_url'] = nel_build_router_url([$this->domain->uri(), 'wordfilters', 'new']);
        $bgclass = 'row1';
        $filters = new Filters($this->database);

        foreach ($filters->getWordfilters([$this->domain->id()]) as $filter) {
            $wordfilter_data = array();
            $filter_domain = Domain::getDomainFromID($filter->getData('board_id'), $this->database);
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $wordfilter_data['bgclass'] = $bgclass;
            $wordfilter_data['filter_id'] = $filter->getData('filter_id');
            $wordfilter_data['board_uri'] = $filter_domain->uri(true);
            $wordfilter_data['text_match'] = $filter->getData('text_match');
            $wordfilter_data['replacement'] = $filter->getData('replacement');
            $wordfilter_data['filter_action'] = $filter->getData('filter_action');
            $wordfilter_data['edit_url'] = nel_build_router_url(
                [$this->domain->uri(), 'wordfilters', $filter->getData('filter_id'), 'modify']);

            if ($filter->getData('enabled') == 1) {
                $wordfilter_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'wordfilters', $filter->getData('filter_id'), 'disable']);
                $wordfilter_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($filter->getData('enabled') == 0) {
                $wordfilter_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'wordfilters', $filter->getData('filter_id'), 'enable']);
                $wordfilter_data['enable_disable_text'] = _gettext('Enable');
            }

            $wordfilter_data['delete_url'] = nel_build_router_url(
                [$this->domain->uri(), 'wordfilters', $filter->getData('filter_id'), 'delete']);
            $this->render_data['wordfilter_list'][] = $wordfilter_data;
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
        $parameters['submit_add'] = true;
        return $this->edit($parameters, $data_only);
    }

    public function edit(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/wordfilters_edit');
        $editing = $parameters['editing'] ?? false;
        $this->render_data['submit_add'] = $parameters['submit_add'] ?? false;
        $this->render_data['submit_edit'] = $editing;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('Wordfilters');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $form_action = '';
        $filter_id = intval($parameters['filter_id'] ?? 0);
        $filter = new Wordfilter($this->database, $filter_id);
        $filter_domain = Domain::getDomainFromID($filter->getData('board_id') ?? $this->domain->id(), $this->database);
        $this->render_data['board_uri'] = $filter_domain->uri(true);

        if ($editing) {
            $form_action = nel_build_router_url([$this->domain->uri(), 'wordfilters', $filter_id, 'modify']);
            $this->render_data['filter_id'] = $filter->getData('filter_id');
            $this->render_data['text_match'] = $filter->getData('text_match');
            $this->render_data['replacement'] = $filter->getData('replacement');
            $this->render_data['enabled_checked'] = $filter->getData('enabled') == 1 ? 'checked' : '';
        } else {
            $form_action = nel_build_router_url([$this->domain->uri(), 'wordfilters', 'new']);
            $this->render_data['enabled_checked'] = 'checked';
        }

        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['boards_select'] = $output_menu->boards('board_id', $this->render_data['board_uri'], true);
        $this->render_data['filter_actions'] = $output_menu->wordfilterActions(
            $filter->getData('filter_action') ?? '');
        $this->render_data['form_action'] = $form_action;
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}