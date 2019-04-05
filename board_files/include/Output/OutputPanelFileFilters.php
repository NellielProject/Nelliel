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
        $this->database = $domain->database();
        $this->domain = $domain;
        $this->utilitySetup();
    }

    public function render(array $parameters = array())
    {
        $user = $parameters['user'];

        if (!$user->domainPermission($this->domain, 'perm_file_filters_access'))
        {
            nel_derp(341, _gettext('You are not allowed to access the File Filters panel.'));
        }

        // Temp
        $this->render_instance = $this->domain->renderInstance();
        $this->render_instance->startTimer();

        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('File Filters')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $template_loader = new \Mustache_Loader_FilesystemLoader($this->domain->templatePath(), ['extension' => '.html']);
        $render_instance = new \Mustache_Engine(['loader' => $template_loader]);
        $template_loader->load('management/panels/file_filters_panel');

        if ($this->domain->id() !== '')
        {
            $prepared = $this->database->prepare(
                    'SELECT * FROM "' . FILE_FILTERS_TABLE . '" WHERE "board_id" = ? ORDER BY "entry" DESC');
            $filters = $this->database->executePreparedFetchAll($prepared, [$this->domain->id()], PDO::FETCH_ASSOC);
        }
        else
        {
            $filters = $this->database->executeFetchAll('SELECT * FROM "' . FILE_FILTERS_TABLE . '" ORDER BY "entry" DESC',
                    PDO::FETCH_ASSOC);
        }

        $render_input['form_action'] = $this->url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'file-filters', 'action' => 'add']);
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

        $this->render_instance->appendToOutput($render_instance->render('management/panels/file_filters_panel', $render_input));
        $output_footer = new \Nelliel\Output\OutputFooter($this->domain);
        $output_footer->render(['dotdot' => '', 'styles' => false]);
        echo $this->render_instance->getOutput();
        nel_clean_exit();
    }
}