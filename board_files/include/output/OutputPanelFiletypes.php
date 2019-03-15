<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use PDO;

class OutputPanelFiletypes extends OutputCore
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

        if (!$user->domainPermission($this->domain, 'perm_filetypes_access'))
        {
            nel_derp(430, _gettext('You are not allowed to access the Filetypes panel.'));
        }

        $this->prepare('management/filetypes_panel.html');
        $output_header = new \Nelliel\Output\OutputHeader($this->domain);
        $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Filetypes')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $filetypes = $this->database->executeFetchAll(
                'SELECT * FROM "' . FILETYPES_TABLE . '" WHERE "extension" <> \'\' ORDER BY "entry" ASC', PDO::FETCH_ASSOC);
        $form_action = $this->url_constructor->dynamic(MAIN_SCRIPT, ['module' => 'filetypes', 'action' => 'add']);
        $this->dom->getElementById('add-filetype-form')->extSetAttribute('action', $form_action);

        $filetype_list = $this->dom->getElementById('filetype-list');
        $filetype_list_nodes = $filetype_list->getElementsByAttributeName('data-parse-id', true);
        $bgclass = 'row1';

        foreach ($filetypes as $filetype)
        {
            $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
            $filetype_row = $this->dom->copyNode($filetype_list_nodes['filetype-row'], $filetype_list, 'append');
            $filetype_row_nodes = $filetype_row->getElementsByAttributeName('data-parse-id', true);
            $filetype_row->extSetAttribute('class', $bgclass);
            $filetype_row_nodes['extension']->setContent($filetype['extension']);
            $filetype_row_nodes['parent-extension']->setContent($filetype['parent_extension']);
            $filetype_row_nodes['type']->setContent($filetype['type']);
            $filetype_row_nodes['format']->setContent($filetype['format']);
            $filetype_row_nodes['mime']->setContent($filetype['mime']);
            $filetype_row_nodes['regex']->setContent($filetype['id_regex']);
            $filetype_row_nodes['label']->setContent($filetype['label']);
            $remove_link = $this->url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'filetypes', 'action' => 'remove', 'filetype-id' => $filetype['entry']]);
            $filetype_row_nodes['filetype-remove-link']->extSetAttribute('href', $remove_link);
        }

        $filetype_list_nodes['filetype-row']->remove();

        $this->domain->translator()->translateDom($this->dom);
        $this->render_instance->appendHTMLFromDOM($this->dom);
        nel_render_general_footer($this->domain);
        echo $this->render_instance->outputRenderSet();
        nel_clean_exit();
    }
}