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

        $this->prepare('management/panels/file_filter_panel.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('File Filters')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);

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

        $form_action = $this->url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'file-filters', 'action' => 'add']);
        $this->dom->getElementById('add-file-filter-form')->extSetAttribute('action', $form_action);

        $filter_list = $this->dom->getElementById('filter-list');
        $filter_list_nodes = $filter_list->getElementsByAttributeName('data-parse-id', true);
        $bgclass = 'row1';

        foreach ($filters as $filter)
        {
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $filter_row = $this->dom->copyNode($filter_list_nodes['file-filter-row'], $filter_list, 'append');
            $filter_row->extSetAttribute('class', $bgclass);
            $filter_row_nodes = $filter_row->getElementsByAttributeName('data-parse-id', true);
            $filter_row_nodes['filter-id']->setContent($filter['entry']);
            $filter_row_nodes['hash-type']->setContent($filter['hash_type']);
            $filter_row_nodes['file-hash']->setContent(bin2hex($filter['file_hash']));
            $filter_row_nodes['file-notes']->setContent($filter['file_notes']);
            $filter_row_nodes['board-id']->setContent($filter['board_id']);
            $remove_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'file-filters', 'action' => 'remove', 'filter-id' => $filter['entry']]);
            $filter_row_nodes['filter-remove-link']->extSetAttribute('href', $remove_link);
        }

        $filter_list_nodes['file-filter-row']->remove();
        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}