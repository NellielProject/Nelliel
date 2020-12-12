<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelFileFilters extends OutputCore
{

    function __construct(Domain $domain, bool $write_mode)
    {
        $this->domain = $domain;
        $this->write_mode = $write_mode;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $user = $parameters['user'];

        if (!$user->checkPermission($this->domain, 'perm_manage_file_filters'))
        {
            nel_derp(340, _gettext('You are not allowed to manage the file filters.'));
        }

        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $this->startTimer();
        $output_head = new OutputHead($this->domain, $this->write_mode);
        $this->render_data['head'] = $output_head->render([], true);
        $output_header = new OutputHeader($this->domain, $this->write_mode);
        $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('File Filters')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'manage_headers' => $manage_headers], true);

        if ($this->domain->id() === '' || $this->domain->id() === '_site_')
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

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('panels/file_filters_panel',
                $this->render_data);
        $output_footer = new OutputFooter($this->domain, $this->write_mode);
        $this->render_data['footer'] = $output_footer->render(['show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}