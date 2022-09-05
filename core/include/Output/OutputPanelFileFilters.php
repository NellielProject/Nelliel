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
            $filter_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(
                    ['module' => 'admin', 'section' => 'file-filters', 'actions' => 'remove',
                        'board-id' => $this->domain->id(), 'filter-id' => $filter['filter_id']]);
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
        $this->setBodyTemplate('panels/file_filters_edit');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('File Filters');
        $parameters['section'] = $parameters['section'] ?? _gettext('Edit');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);
        $editing = $parameters['editing'] ?? true;
        $filter = $this->database->executeFetch('SELECT * FROM "' . NEL_FILE_FILTERS_TABLE . '"', PDO::FETCH_ASSOC);
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
            http_build_query(
                ['module' => 'admin', 'section' => 'file-filters', 'actions' => 'add',
                    'board-id' => $this->domain->id()]);

        $this->render_data['hash_type'] = $filter['hash_type'];
        $this->render_data['file_hash'] = $filter['file_hash'];
        $this->render_data['notes'] = $filter['notes'];
        $this->render_data['board_id'] = $filter['board_id'];
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render([], true);
        $output = $this->output('basic_page', $data_only, true, $this->render_data);
        echo $output;
        return $output;
    }
}