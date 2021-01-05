<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

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
        $this->setBodyTemplate('panels/file_filters');
        $parameters['is_panel'] = true;
        $parameters['panel'] = $parameters['panel'] ?? _gettext('File Filters');
        $parameters['section'] = $parameters['section'] ?? _gettext('Main');
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $this->render_data['header'] = $output_header->manage($parameters, true);

        if ($this->domain->id() === Domain::SITE)
        {
            $filters = $this->database->executeFetchAll(
                    'SELECT * FROM "' . NEL_FILES_FILTERS_TABLE . '" ORDER BY "entry" DESC', PDO::FETCH_ASSOC);
        }
        else
        {
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . NEL_FILES_FILTERS_TABLE . '" WHERE "board_id" = ? ORDER BY "entry" DESC');
            $filters = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        }

        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                http_build_query(['module' => 'admin', 'section' => 'file-filters', 'actions' => 'add']);
        $bgclass = 'row1';

        foreach ($filters as $filter)
        {
            $filter_data = array();
            $filter_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $filter_data['filter_id'] = $filter['entry'];
            $filter_data['hash_type'] = $filter['hash_type'];
            $filter_data['file_hash'] = bin2hex($filter['file_hash']);
            $filter_data['file_notes'] = $filter['file_notes'];
            $filter_data['board_id'] = $filter['board_id'];
            $filter_data['remove_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                    http_build_query(
                            ['module' => 'admin', 'section' => 'file-filters', 'actions' => 'remove',
                                'filter-id' => $filter['entry']]);
            $this->render_data['filter_list'][] = $filter_data;
        }

        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}