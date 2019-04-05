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
    private $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_file_filters_access'))
        {
            nel_derp(341, _gettext('You are not allowed to access the File Filters panel.'));
        }

        $this->render_core->startTimer();
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('File Filters')];
        $this->render_core->appendToOutput(
                $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]));

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

        $render_input['form_action'] = $this->url_constructor->dynamic(MAIN_SCRIPT,
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
            $render_input['filter_list'][] = $filter_data;
        }

        $this->render_core->appendToOutput(
                $this->render_core->renderFromTemplateFile('management/panels/file_filters_panel', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $this->render_core->appendToOutput($output_footer->render(['dotdot' => '', 'generate_styles' => false]));
        echo $this->render_core->getOutput();
        nel_clean_exit();
    }
}