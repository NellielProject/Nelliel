<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Filters\FileFilter;
use Nelliel\Filters\Filters;

class OutputPanelFileFilters extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setBodyTemplate('panels/file_filters_main');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('File Filters');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['new_url'] = nel_build_router_url([$this->domain->uri(), 'file-filters', 'new']);
        $this->render_data['form_action'] = nel_build_router_url([$this->domain->uri(), 'file-filters', 'new']);
        $bgclass = 'row1';
        $filters = new Filters($this->database);

        foreach ($filters->getFileFilters([$this->domain->id()]) as $filter) {
            $filter_domain = Domain::getDomainFromID($filter->getData('board_id'));
            $filter_data = array();
            $filter_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $filter_data['filter_id'] = $filter->getData('filter_id');
            $filter_data['file_hash'] = $filter->getData('file_hash');
            $filter_data['notes'] = $filter->getData('notes');
            $filter_data['board_uri'] = $filter_domain->uri(true);
            $filter_data['filter_action'] = $filter->getData('filter_action');

            $filter_data['edit_url'] = nel_build_router_url(
                [$this->domain->uri(), 'file-filters', $filter->getData('filter_id'), 'modify']);

            if ($filter->getData('enabled')) {
                $filter_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'file-filters', $filter->getData('filter_id'), 'disable']);
                $filter_data['enable_disable_text'] = _gettext('Disable');
            } else {
                $filter_data['enable_disable_url'] = nel_build_router_url(
                    [$this->domain->uri(), 'file-filters', $filter->getData('filter_id'), 'enable']);
                $filter_data['enable_disable_text'] = _gettext('Enable');
            }

            $filter_data['delete_url'] = nel_build_router_url(
                [$this->domain->uri(), 'file-filters', $filter->getData('filter_id'), 'delete']);

            $this->render_data['filter_list'][] = $filter_data;
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
        $editing = $parameters['editing'] ?? true;
        $this->setBodyTemplate('panels/file_filters_edit');
        $parameters['panel'] = $parameters['panel'] ?? _gettext('File Filters');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['board_id'] = '';
        $filter_id = intval($parameters['filter_id'] ?? 0);
        $filter = new FileFilter($this->database, $filter_id);
        $filter_domain = Domain::getDomainFromID($filter->getData('board_id') ?? $this->domain->id(), $this->database);
        $this->render_data['board_uri'] = $filter_domain->uri();

        if ($editing) {
            $this->render_data['form_action'] = nel_build_router_url(
                [$this->domain->uri(), 'file-filters', $filter->getData('filter_id'), 'modify']);
            $this->render_data['file_hash'] = $filter->getData('file_hash');
            $this->render_data['notes'] = $filter->getData('notes');
            $this->render_data['enabled'] = $filter->getData('enabled');
        } else {
            $this->render_data['form_action'] = nel_build_router_url([$this->domain->uri(), 'file-filters', 'new']);
            $this->render_data['enabled_checked'] = 'checked';
        }

        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['boards_select'] = $output_menu->boards('board_id', $this->render_data['board_uri'], true);
        $this->render_data['filter_actions'] = $output_menu->fileFilterActions($filter->getData('filter_action') ?? '');
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->manage([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}