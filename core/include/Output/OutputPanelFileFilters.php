<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class OutputPanelFileFilters extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $this->setBodyTemplate('panels/file_filters_main');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('File Filters');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        if ($this->domain->id() === Domain::SITE) {
            $filters = $this->database->executeFetchAll('SELECT * FROM "' . NEL_FILE_FILTERS_TABLE . '"',
                PDO::FETCH_ASSOC);
        } else {
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_FILE_FILTERS_TABLE . '" WHERE "board_id" = ?');
            $filters = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        }

        $this->render_data['new_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
            http_build_query(['module' => 'admin', 'section' => 'file-filters', 'actions' => 'new']);
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
            http_build_query(
                ['module' => 'admin', 'section' => 'file-filters', 'actions' => 'add',
                    'board-id' => $this->domain->id()]);
        $bgclass = 'row1';

        foreach ($filters as $filter) {
            $filter_data = array();
            $filter_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $filter_data['filter_id'] = $filter['filter_id'];
            $filter_data['hash_type'] = $filter['hash_type'];
            $filter_data['file_hash'] = $filter['file_hash'];
            $filter_data['notes'] = $filter['notes'];
            $filter_data['board_id'] = $filter['board_id'];

            $filter_data['edit_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'file-filters', 'actions' => 'edit',
                        'filter-id' => $filter_data['filter_id']]);

            if ($filter['enabled'] == 1) {
                $filter_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                        ['module' => 'admin', 'section' => 'file-filters', 'actions' => 'disable',
                            'filter-id' => $filter_data['filter_id']]);
                $filter_data['enable_disable_text'] = _gettext('Disable');
            }

            if ($filter['enabled'] == 0) {
                $filter_data['enable_disable_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                        ['module' => 'admin', 'section' => 'file-filters', 'actions' => 'enable',
                            'filter-id' => $filter_data['filter_id']]);
                $filter_data['enable_disable_text'] = _gettext('Enable');
            }

            $filter_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'file-filters', 'actions' => 'remove',
                        'filter-id' => $filter['filter_id']]);

            $this->render_data['filter_list'][] = $filter_data;
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
        $editing = $parameters['editing'] ?? true;
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('File Filters');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $this->render_data['board_id'] = '';

        if ($editing) {
            $this->setBodyTemplate('panels/file_filters_edit');
            $filter_id = $parameters['filter_id'] ?? 0;
            $prepared = $this->database->prepare('SELECT * FROM "' . NEL_FILE_FILTERS_TABLE . '" WHERE "filter_id" = ?');
            $filter_data = $this->database->executePreparedFetch($prepared, [$filter_id], PDO::FETCH_ASSOC);
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'file-filters', 'actions' => 'update', 'filter-id' => $filter_id]);

            $this->render_data['hash_type'] = $filter_data['hash_type'];
            $this->render_data['file_hash'] = $filter_data['file_hash'];
            $this->render_data['notes'] = $filter_data['notes'];
            $this->render_data['board_id'] = $filter_data['board_id'];
            $this->render_data['enabled'] = $filter_data['enabled'];
        } else {
            $this->setBodyTemplate('panels/file_filters_new');
            $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'file-filters', 'actions' => 'add']);
        }

        $output_menu = new OutputMenu($this->domain, $this->write_mode);
        $this->render_data['boards_select'] = $output_menu->boards('board_id', $this->render_data['board_id'], true);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}