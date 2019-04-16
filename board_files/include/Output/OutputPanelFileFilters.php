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

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_file_filters_access'))
        {
            nel_derp(341, _gettext('You are not allowed to access the File Filters panel.'));
        }

        $this->startTimer();
        $dotdot = $parameters['dotdot'] ?? '';
        $output_head = new OutputHead($this->domain);
        $this->render_data['head'] = $output_head->render(['dotdot' => $dotdot], true);
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $manage_headers = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('File Filters')];
        $this->render_data['header'] = $output_header->render(
                ['header_type' => 'general', 'dotdot' => $dotdot, 'manage_headers' => $manage_headers], true);

        if ($this->domain->id() !== '')
        {
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . FILE_FILTERS_TABLE . '" WHERE "board_id" = ? ORDER BY "entry" DESC');
            $filters = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        }
        else
        {
            $filters = $this->database->executeFetchAll(
                    'SELECT * FROM "' . FILE_FILTERS_TABLE . '" ORDER BY "entry" DESC', PDO::FETCH_ASSOC);
        }

        $this->render_data['form_action'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                ['module' => 'file-filters', 'action' => 'add']);
        $bgclass = 'row1';

        foreach ($filters as $filter)
        {
            $filter_data = array();
            $filter_data['bgclass'] = $bgclass;
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $filter_data['entry'] = $filter['entry'];
            $filter_data['hash_type'] = $filter['hash_type'];
            $filter_data['file_hash'] = bin2hex($filter['file_hash']);
            $filter_data['file_notes'] = $filter['file_notes'];
            $filter_data['board_id'] = $filter['board_id'];
            $filter_data['remove_url'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'file-filters', 'action' => 'remove', 'filter-id' => $filter['entry']]);
            $this->render_data['filter_list'][] = $filter_data;
        }

        $this->render_data['body'] = $this->render_core->renderFromTemplateFile('management/panels/file_filters_panel',
                $this->render_data);
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_data['footer'] = $output_footer->render(['dotdot' => $dotdot, 'show_styles' => false], true);
        $output = $this->output('basic_page', $data_only, true);
        echo $output;
        return $output;
    }
}